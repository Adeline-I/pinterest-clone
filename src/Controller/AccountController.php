<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\UserFormType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class AccountController extends AbstractController
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
     * @Security("is_granted('ROLE_USER')")
     */
    public function show(): Response
    {
        // if (!$this->getUser()) {
        //     $this->addFlash('error', 'Vous n\'êtes pas encore connecté !');
        //     return $this->redirectToRoute('app_login');
        // }

        return $this->render('account/show.html.twig');
    }

    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function edit(Request $request): Response
    {
        // if (!$this->getUser()) {
        //     $this->addFlash('error', 'Vous n\'êtes pas encore connecté !');
        //     return $this->redirectToRoute('app_login');
        // }

        $user = $this->getUser();

        $form = $this->createForm(UserFormType::class, $user, [
            'method' => 'PATCH'
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'Profil modifié avec succès !');

            return $this->redirectToRoute('app_account');
        }

        return $this->render('account/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function changePassword(Request $request, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        // if (!$this->getUser()) {
        //     $this->addFlash('error', 'Vous n\'êtes pas encore connecté !');
        //     return $this->redirectToRoute('app_login');
        // }

        $user = $this->getUser();

        $form = $this->createForm(ChangePasswordFormType::class, null, [
            'current_password_is_required' => true,
            'method' => 'PATCH'
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $this->em->flush();
            $this->addFlash('success', 'Mot de passe modifié avec succès !');

            return $this->redirectToRoute('app_account');
        }

        return $this->render('account/change_password.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
