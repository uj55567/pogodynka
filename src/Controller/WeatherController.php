<?php

namespace App\Controller;

use App\Entity\Weather;
use App\Form\Weather1Type;
use App\Entity\Location;
use App\Repository\WeatherRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/weather')]
final class WeatherController extends AbstractController
{
    // 1. Lista wszystkich pomiarów (CRUD)
    #[Route('', name: 'app_weather_index', methods: ['GET'])]
    #[IsGranted('ROLE_WEATHER_INDEX')]
    public function index(WeatherRepository $weatherRepository): Response
    {
        return $this->render('weather/index.html.twig', [
            'weathers' => $weatherRepository->findAll(),
        ]);
    }

    // 2. Tworzenie nowego pomiaru z grupą walidacyjną 'new'
    #[Route('/new', name: 'app_weather_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_WEATHER_NEW')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $weather = new Weather();
        $form = $this->createForm(Weather1Type::class, $weather, [
            'validation_groups' => ['Default', 'new'],
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($weather);
            $entityManager->flush();

            return $this->redirectToRoute('app_weather_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('weather/new.html.twig', [
            'weather' => $weather,
            'form' => $form,
        ]);
    }

    // 3. Szczegóły pojedynczego pomiaru (wymóg standardowego CRUD)
    #[Route('/{id}', name: 'app_weather_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_WEATHER_SHOW')]
    public function show(Weather $weather): Response
    {
        return $this->render('weather/show.html.twig', [
            'weather' => $weather,
        ]);
    }

    // 4. Edycja pomiaru z grupą walidacyjną 'edit'
    #[Route('/{id}/edit', name: 'app_weather_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_WEATHER_EDIT')]
    public function edit(Request $request, Weather $weather, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Weather1Type::class, $weather, [
            'validation_groups' => ['Default', 'edit'],
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_weather_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('weather/edit.html.twig', [
            'weather' => $weather,
            'form' => $form,
        ]);
    }

    // 5. Twój unikalny widok dla konkretnego miasta i kraju
    #[Route('/{country}/{city}', name: 'app_weather_city', requirements: ['country' => '[A-Za-z]{2}'])]
    public function city(
        #[MapEntity(mapping: ['country' => 'country', 'city' => 'city_name'])]
        Location $location,
        WeatherRepository $repository
    ): Response {
        $weathers = $repository->findByLocation($location);

        return $this->render('weather/city.html.twig', [
            'location' => $location,
            'weathers' => $weathers,
        ]);
    }

    // 6. Usuwanie pomiaru
    #[Route('/{id}', name: 'app_weather_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_WEATHER_DELETE')]
    public function delete(Request $request, Weather $weather, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$weather->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($weather);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_weather_index', [], Response::HTTP_SEE_OTHER);
    }
}
