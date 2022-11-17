<?php

namespace App\Controller;

use App\Entity\Pin;
use App\Entity\User;
use App\Form\DeleteType;
use App\Form\PinType;
use App\Repository\PinRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PinsController extends AbstractController
{
    // Création d'un attribut
    private $em;
    // Création de la méthode construct
    public function __construct(EntityManagerInterface $em)
    {
        // Affectation de $em à l'attribut $em
        $this->em = $em;
    }

    /**
     * Injection de dépendance(s) dans les paramètres
     * 
     * @Route("/", name="app_home", methods={"GET"})
     */
    public function index(PinRepository $pinRepository): Response
    {
        // RECUPERATION DE DONNEES
        $pins = $pinRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('pins/index.html.twig', compact(
            'pins',
        ));
    }

    /**
     * Injection de dépendance(s) dans les paramètres
     * Ajout de UserRepository de manière temporaire pour la création de pins avec un user définit
     * 
     * @Route("/epingles/creer", name="app_pins_create", methods={"GET","POST"})
     * @Security("is_granted('ROLE_USER') && user.isVerified()")
     */
    public function create(Request $request): Response
    {
        // if (!$this->getUser()) {
        //     $this->addFlash('error', 'Vous n\'êtes pas encore connecté !');
        //     return $this->redirectToRoute('app_login');
        // }

        // if (!$this->getUser()->isVerified()) {
        //     $this->addFlash('error', 'Vous devez avoir un compte vérifié !');
        //     return $this->redirectToRoute('app_home');
        // }

        // Vous instanciez et travaillez avec l'objet $pin comme n'importe quel autre objet PHP normal.
        $pin = new Pin;

        // Création du formulaire gràce à l'objet PinType créer grâce à make:form
        $form = $this->createForm(PinType::class, $pin);

        // Récupération des données envoyées en POST
        $form->handleRequest($request);

        // Vérification de la soumission du formulaire et la validité des données
        if ($form->isSubmitted() && $form->isValid()) {
            $pin->setUser($this->getUser());

            // L'appel persist($pin) indique à Doctrine de "gérer" l'objet $pin avec les attributs "settés". Cela n'entraîne pas l'envoi d'une requête à la base de données.
            $this->em->persist($pin);

            // Lorsque la méthode flush() est appelée, Doctrine parcourt tous les objets qu'il gère pour voir s'ils doivent être conservés dans la base de données. Ici, les données $pin de l'objet n'existent pas dans la base de données, donc l'Entity Manager exécute une requête INSERT, créant une nouvelle ligne dans la table pins.
            $this->em->flush();

            // Ajout d'un message flash
            $this->addFlash('success', 'Épingle créée avec succès !');

            // Redirection après l'envoi des données
            return $this->redirectToRoute('app_pins_show', ['id' => $pin->getId()]);
        }

        return $this->render('pins/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Injection de l'entité Pin dans les paramètres en se basant sur l'id reçu en get (disponible via la notion de ParamConverter de sensio/framework-extra-bundle)
     * Une exception sera automatiquement levé en cas de valeur null (erreur 404)
     * 
     * @Route("/epingles/{id}", name="app_pins_show", methods={"GET"}, requirements={"id"="[0-9]+"})
     */
    public function show(Pin $pin): Response
    {
        return $this->render('pins/show.html.twig', compact(
            'pin',
        ));
    }

    /**
     * Injection de l'entité Pin dans les paramètres en se basant sur l'id reçu en get (disponible via la notion de ParamConverter de sensio/framework-extra-bundle)
     * Une exception sera automatiquement levé en cas de valeur null (erreur 404)
     * 
     * @Route("/epingles/{id}/modifier", name="app_pins_edit", methods={"GET","PUT"}, requirements={"id"="[0-9]+"})
     * @IsGranted("PIN_EDIT", subject="pin")
     */
    public function edit(Pin $pin, Request $request): Response
    {
        // if (!$this->getUser()) {
        //     $this->addFlash('error', 'Vous n\'êtes pas encore connecté !');
        //     return $this->redirectToRoute('app_login');
        // }

        // if (!$this->getUser()->isVerified()) {
        //     $this->addFlash('error', 'Vous devez avoir un compte vérifié !');
        //     return $this->redirectToRoute('app_home');
        // }

        // if ($pin->getUser() != $this->getUser()) {
        //     $this->addFlash('error', 'Accès refusé !');
        //     return $this->redirectToRoute('app_home');
        // }

        // $this->denyAccessUnlessGranted('PIN_EDIT', $pin);

        // Création du formulaire gràce à l'objet PinType créer grâce à make:form
        $form = $this->createForm(PinType::class, $pin, [
            'method' => 'PUT',
        ]);

        // Récupération des données envoyées en POST
        $form->handleRequest($request);

        // Vérification de la soumission du formulaire et la validité des données
        if ($form->isSubmitted() && $form->isValid()) {

            // Lorsque la méthode flush() est appelée, Doctrine parcourt tous les objets qu'il gère pour voir s'ils doivent être conservés dans la base de données. Ici, les données $pin de l'objet n'existent pas dans la base de données, donc l'Entity Manager exécute une requête INSERT, créant une nouvelle ligne dans la table pins.
            $this->em->flush();

            // Ajout d'un message flash
            $this->addFlash('success', 'Épingle modifiée avec succès !');

            // Redirection après l'envoi des données
            return $this->redirectToRoute('app_pins_show', ['id' => $pin->getId()]);
        }

        return $this->render('pins/edit.html.twig', [
            'pin' => $pin,
            'form' => $form->createView()
        ]);
    }

    /**
     * Injection de l'entité Pin dans les paramètres en se basant sur l'id reçu en get (disponible via la notion de ParamConverter de sensio/framework-extra-bundle)
     * Une exception sera automatiquement levé en cas de valeur null (erreur 404)
     * 
     * @Route("/epingles/{id}/supprimer", name="app_pins_delete", methods={"DELETE"}, requirements={"id"="[0-9]+"})
     * @Security("is_granted('PIN_DELETE', pin)")
     */
    public function delete(Request $request, Pin $pin): Response
    {
        // if (!$this->getUser()) {
        //     $this->addFlash('error', 'Vous n\'êtes pas encore connecté !');
        //     return $this->redirectToRoute('app_login');
        // }

        // if (!$this->getUser()->isVerified()) {
        //     $this->addFlash('error', 'Vous devez avoir un compte vérifié !');
        //     return $this->redirectToRoute('app_home');
        // }

        // if ($pin->getUser() != $this->getUser()) {
        //     $this->addFlash('error', 'Accès refusé !');
        //     return $this->redirectToRoute('app_home');
        // }

        // Vérification de la conformité du token
        if ($this->isCsrfTokenValid('pinsdeletion' . $pin->getId(), $request->request->get('csrf_token'))) {

            // L'appel remove($pin) indique à Doctrine de "gérer" l'objet $pin avec les attributs "settés". Cela n'entraîne pas l'envoi d'une requête à la base de données.
            $this->em->remove($pin);

            // Lorsque la méthode flush() est appelée, Doctrine parcourt tous les objets qu'il gère pour voir s'ils doivent être conservés dans la base de données. Ici, l'Entity Manager exécute une requête DELETE, supprimant une ligne dans la table pins.
            $this->em->flush();
        }

        // Redirection après l'envoi des données
        return $this->redirectToRoute('app_home');
    }
}
