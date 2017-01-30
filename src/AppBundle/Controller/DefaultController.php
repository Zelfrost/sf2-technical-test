<?php

namespace AppBundle\Controller;

use AppBundle\Form\Type\SearchUserType;
use GuzzleHttp\Exception\RequestException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends Controller
{
    /**
     * @Route(path="/", name="index")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $form = $this->createForm(SearchUserType::class);
        $form->handleRequest($request);

        $users = [];

        if ($form->isValid()) {
            $data = $form->getData();

            $finder = $this->get('app.finder.github');

            try {
                $users = $finder->findUsers($data['username']);

                if (empty($users)) {
                    $form->get('username')->addError(new FormError('Aucun utilisateur ne correspond à ce login'));
                }
            } catch (RequestException $e) {
                $form->get('username')->addError(new FormError('Github ne peut actuellement pas répondre'));
            }
        }

        return [
            'form' => $form->createView(),
            'users' => $users,
        ];
    }
}
