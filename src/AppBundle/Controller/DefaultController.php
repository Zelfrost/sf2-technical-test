<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Comment;
use AppBundle\Form\Type\CommentType;
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
                $form->get('username')->addError(new FormError('Github ne répond pas'));
            }
        }

        return [
            'form' => $form->createView(),
            'users' => $users,
        ];
    }

    /**
     * @Route(path="/{username}/comment", name="comment")
     * @Template()
     */
    public function commentAction(Request $request, $username)
    {
        $finder = $this->get('app.finder.github');
        $manager = $this->getDoctrine()->getManager();
        $successMessage = null;

        try {
            $repositories = $finder->findRepositories($username);
        } catch (RequestException $e) {
            return [
                'username' => $username,
                'error_message' => 'Cet utilisateur n\'existe pas ou n\'a pas de repository',
            ];
        }

        $comment = new Comment();
        $comment->setUsername($username);

        $form = $this->createForm(CommentType::class, $comment, ['repositories' => $repositories]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $comment->getRepository() === null) {
            $form->get('repository')->addError(new FormError('Veuillez choisir un des dépôts de la liste'));
        }

        if ($form->isValid()) {
            $manager->persist($comment);
            $manager->flush();

            $successMessage = 'Votre commentaire a bien été ajouté';
        }

        $comments = $manager->getRepository(Comment::class)
            ->findByUsername($username);

        return [
            'username' => $username,
            'form' => $form->createView(),
            'comments' => $comments,
            'success_message' => $successMessage,
        ];
    }
}
