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

        if (!$form->isValid()) {
            return [
                'form' => $form->createView(),
            ];
        }

        $data = $form->getData();

        $finder = $this->get('app.finder.github');

        try {
            $users = $finder->findUsers($data['username']);
        } catch (RequestException $e) {
            $form->get('username')->addError(new FormError('Github ne répond pas'));

            return ['form' => $form->createView()];
        }

        if (empty($users)) {
            $form->get('username')->addError(new FormError('Aucun utilisateur ne correspond à ce login'));
        }

        if (count($users) === 1) {
            return $this->redirect($this->generateUrl('comment', ['username' => $users[0]]));
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

        try {
            $repositories = $finder->findRepositories($username);
        } catch (RequestException $e) {
            $this->get('session')->getFlashBag()->add(
                'error',
                'L\'utilisateur que vous avez demandé n\'existe pas, ou n\'a pas de dépôts'
            );

            return $this->redirect($this->generateUrl('index'));
        }

        $comment = new Comment();
        $comment->setUsername($username);
        $comment->setAuthor($this->getUser());

        $form = $this->createForm(CommentType::class, $comment, ['repositories' => $repositories]);
        $form->handleRequest($request);

        $comments = $manager->getRepository(Comment::class)->findBy(
            ['username' => $username],
            ['id' => 'desc']
        );

        if ($form->isValid()) {
            $manager->persist($comment);
            $manager->flush();

            array_unshift($comments, $comment);

            return [
                'username' => $username,
                'form' => $form->createView(),
                'comments' => $comments,
                'success_message' => 'Votre commentaire a bien été ajouté',
            ];
        }

        if ($form->isSubmitted() && $comment->getRepository() === null) {
            $form->get('repository')->addError(new FormError('Veuillez choisir un des dépôts de la liste'));
        }

        return [
            'username' => $username,
            'form' => $form->createView(),
            'comments' => $comments,
        ];
    }
}
