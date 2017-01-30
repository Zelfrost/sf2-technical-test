<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\Type\RegisterType;
use AppBundle\Form\Type\LoginType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends Controller
{
    /**
     * @Route(path="/login", name="login")
     * @Template()
     */
    public function loginAction(Request $request)
    {
        if ($this->getUser() !== null) {
            return $this->redirect($this->generateUrl('home'));
        }

        $authenticationUtils = $this->get('security.authentication_utils');

        $form = $this->createForm(LoginType::class);

        return [
            'form' => $form->createView(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ];
    }

    /**
     * @Route(path="/register", name="register")
     * @Template()
     */
    public function registerAction(Request $request)
    {
        if ($this->getUser() !== null) {
            return $this->redirect($this->generateUrl('home'));
        }

        $user = new User();

        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $password = $this->get('security.password_encoder')->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($user);
            $manager->flush();

            $this->get('session')->getFlashBag()->add('success', 'Votre compte a bien été créé.');

            return $this->redirect($this->generateUrl('login'));
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
