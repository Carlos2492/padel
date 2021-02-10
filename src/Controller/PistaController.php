<?php

namespace App\Controller;

use App\Entity\Pista;
use App\Form\PistaType;
use App\Repository\PistaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/pista")
 */
class PistaController extends AbstractController
{
    /**
     * @Route("/", name="pista_index", methods={"GET"})
     */
    public function index(PistaRepository $pistaRepository): Response
    {
        return $this->render('pista/index.html.twig', [
            'pistas' => $pistaRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="pista_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $pistum = new Pista();
        $form = $this->createForm(PistaType::class, $pistum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($pistum);
            $entityManager->flush();

            return $this->redirectToRoute('pista_index');
        }

        return $this->render('pista/new.html.twig', [
            'pistum' => $pistum,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="pista_show", methods={"GET"})
     */
    public function show(Pista $pistum): Response
    {
        return $this->render('pista/show.html.twig', [
            'pistum' => $pistum,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="pista_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Pista $pistum): Response
    {
        $form = $this->createForm(PistaType::class, $pistum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('pista_index');
        }

        return $this->render('pista/edit.html.twig', [
            'pistum' => $pistum,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="pista_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Pista $pistum): Response
    {
        if ($this->isCsrfTokenValid('delete'.$pistum->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($pistum);
            $entityManager->flush();
        }

        return $this->redirectToRoute('pista_index');
    }
}
