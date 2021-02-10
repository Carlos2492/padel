<?php

namespace App\Controller;

use App\Entity\Horas;
use App\Form\HorasType;
use App\Repository\HorasRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/horas")
 */
class HorasController extends AbstractController
{
    /**
     * @Route("/", name="horas_index", methods={"GET"})
     */
    public function index(HorasRepository $horasRepository): Response
    {
        return $this->render('horas/index.html.twig', [
            'horas' => $horasRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="horas_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $hora = new Horas();
        $form = $this->createForm(HorasType::class, $hora);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($hora);
            $entityManager->flush();

            return $this->redirectToRoute('horas_index');
        }

        return $this->render('horas/new.html.twig', [
            'hora' => $hora,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="horas_show", methods={"GET"})
     */
    public function show(Horas $hora): Response
    {
        return $this->render('horas/show.html.twig', [
            'hora' => $hora,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="horas_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Horas $hora): Response
    {
        $form = $this->createForm(HorasType::class, $hora);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('horas_index');
        }

        return $this->render('horas/edit.html.twig', [
            'hora' => $hora,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="horas_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Horas $hora): Response
    {
        if ($this->isCsrfTokenValid('delete'.$hora->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($hora);
            $entityManager->flush();
        }

        return $this->redirectToRoute('horas_index');
    }
}
