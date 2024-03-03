<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\Categorie;
use App\Entity\Jeux;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\JeuxType;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Knp\Component\Pager\PaginatorInterface;

class JeuxController extends AbstractController
{
    #[Route('/jeux', name: 'app_jeux')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $jeuxRepository = $entityManager->getRepository(Jeux::class);
        $jeux = $jeuxRepository->findAll();

        return $this->render('jeux/index.html.twig', [
            'jeux' => $jeux,
            'controller_name' => 'JeuxController',
        ]);
    }
    #[Route('/jeux/Home', name: 'Home_display')]
    public function Home(EntityManagerInterface $entityManager): Response
    {
        $jeuxRepository = $entityManager->getRepository(Jeux::class);
        $jeux = $jeuxRepository->findAll();

        return $this->render('Home/Home.html.twig', [
            'jeux' => $jeux,
            'controller_name' => 'JeuxController',
        ]);
    }

    #[Route('/jeux/create', name: 'jeux_create')]
    public function create(EntityManagerInterface $entityManager, Request $request, #[Autowire('%photo_dir%')] string $photoDir): Response
    {
        $jeu = new Jeux();
        $form = $this->createForm(JeuxType::class, $jeu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($jeu);
            if($photo = $form['photo']->getData()){
            $fileName = uniqid().'.'.$photo->guessExtension();
            $photo->move($photoDir,$fileName);
                $jeu->setImagej($fileName);
            }

            $entityManager->flush();

            $this->addFlash('notice', 'Jeux created successfully!');

            return $this->redirectToRoute('app_jeux');
        }

        return $this->render('jeux/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/jeux/update/{id}', name: 'jeux_update')]
    public function update(EntityManagerInterface $entityManager, Request $request, $id , #[Autowire('%photo_dir%')] string $photoDir): Response
    {
        $jeuxRepository = $entityManager->getRepository(Jeux::class);
        $jeu = $jeuxRepository->find($id);

        if (!$jeu) {
            throw $this->createNotFoundException('Jeux not found');
        }

        $form = $this->createForm(JeuxType::class, $jeu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if($photo = $form['photo']->getData()){
                $fileName = uniqid().'.'.$photo->guessExtension();
                $photo->move($photoDir,$fileName);
                $jeu->setImagej($fileName);
            }
            $entityManager->flush();

            $this->addFlash('notice', 'Jeux updated successfully!');

            return $this->redirectToRoute('app_jeux');
        }

        return $this->render('jeux/update.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/jeux/delete/{id}', name: 'jeux_delete')]
    public function delete(EntityManagerInterface $entityManager, $id): Response
    {
        $jeuxRepository = $entityManager->getRepository(Jeux::class);
        $jeu = $jeuxRepository->find($id);

        if (!$jeu) {
            throw $this->createNotFoundException('Jeux not found');
        }

        $entityManager->remove($jeu);
        $entityManager->flush();

        $this->addFlash('notice', 'Jeux deleted successfully!');

        return $this->redirectToRoute('app_jeux');
    }

    #[Route('/jeux/store-jeux', name: 'store-jeux')]
    public function store(EntityManagerInterface $entityManager): Response
    {
        $jeuxRepository = $entityManager->getRepository(Jeux::class);
        $jeux = $jeuxRepository->findAll();

        return $this->render('jeux/store-jeux.html.twig', [
            'list' => $jeux,
            'noResults' => empty($data),
            'controller_name' => 'MainController',

        ]);
    }
    #[Route('/Search', name: 'search_jeux')]
    public function searchJeux(Request $request, EntityManagerInterface $entityManager): Response
    {
        $keyword = $request->query->get('keyword');
        $jeuxRepository = $entityManager->getRepository(Jeux::class);

        if (!empty($keyword)) {
            $data = $jeuxRepository->findByKeyword($keyword);
        } else {
            $data = $jeuxRepository->findAll();
        }

        $html = $this->renderView('jeux/store-jeux.html.twig', [
            'list' => $data,
            'noResults' => empty($data),
            'keyword' => $keyword ?? '',
        ]);

        return new JsonResponse(['html' => $html, 'list' => $data]);
    }
    #[Route('/jeux/pc', name: 'jeux_pc')]
    public function pcGames(EntityManagerInterface $entityManager): Response
    {
        $categorieName = 'PC';
        $categorie = $entityManager->getRepository(Categorie::class)->findOneBy(['Type' => $categorieName]);

        if (!$categorie) {
            throw $this->createNotFoundException('Category PC not found.');
        }

        $jeuxRepository = $entityManager->getRepository(Jeux::class);
        $jeux = $jeuxRepository->findBy(['categorie' => $categorie]);

        return $this->render('jeux/store-jeux.html.twig', [
            'list' => $jeux,
            'noResults' => empty($data),
            'controller_name' => 'JeuxController',
        ]);
    }

    #[Route('/jeux/ps4', name: 'jeux_ps4')]
    public function ps4Games(EntityManagerInterface $entityManager): Response
    {
        $categorieName = 'PS';
        $categorie = $entityManager->getRepository(Categorie::class)->findOneBy(['Type' => $categorieName]);

        if (!$categorie) {
            throw $this->createNotFoundException('Category PS4 not found.');
        }

        $jeuxRepository = $entityManager->getRepository(Jeux::class);
        $jeux = $jeuxRepository->findBy(['categorie' => $categorie]);

        return $this->render('jeux/store-jeux.html.twig', [
            'list' => $jeux,
            'noResults' => empty($data),
            'controller_name' => 'JeuxController',
        ]);
    }

    #[Route('/jeux/xbox', name: 'jeux_xbox')]
    public function xboxGames(EntityManagerInterface $entityManager): Response
    {
        $categorieName = 'Xbox';
        $categorie = $entityManager->getRepository(Categorie::class)->findOneBy(['Type' => $categorieName]);

        if (!$categorie) {
            throw $this->createNotFoundException('Category Xbox not found.');
        }

        $jeuxRepository = $entityManager->getRepository(Jeux::class);
        $jeux = $jeuxRepository->findBy(['categorie' => $categorie]);

        return $this->render('jeux/store-jeux.html.twig', [
            'list' => $jeux,
            'noResults' => empty($data),
            'controller_name' => 'JeuxController',
        ]);
    }
    #[Route('/all-jeux', name: 'all_jeux')]
    public function allJeux(EntityManagerInterface $entityManager): Response
    {
        $jeuxRepository = $entityManager->getRepository(Jeux::class);
        $jeux = $jeuxRepository->findAll();

        return $this->render('jeux/store-jeux.html.twig', [
            'list' => $jeux,
            'noResults' => empty($data),
            'controller_name' => 'JeuxController',
        ]);
    }
    #[Route('/search-prix', name: 'search_prix')]
    public function searchPrix(Request $request, EntityManagerInterface $entityManager): Response
    {
        $minPrice = $request->query->get('minPrice', 0);
        $maxPrice = $request->query->get('maxPrice', 0);


        $jeuxRepository = $entityManager->getRepository(Jeux::class);
        $jeux = $jeuxRepository->findByPriceRange($minPrice, $maxPrice);

        return $this->render('jeux/store-jeux.html.twig', [
            'list' => $jeux,
            'noResults' => empty($jeux),
            'controller_name' => 'JeuxController',
        ]);
    }

    #[Route('/update-cart-item-jeux/{id}', name: 'update_cart_item_jeux', methods: ['POST'])]
    public function updateCartItemJeux($id, Request $request, SessionInterface $session, EntityManagerInterface $entityManager): JsonResponse
    {
        $quantity = $request->request->get('quantity');
        $cart = $session->get('cart_jeux', []);
        $cart[$id] = $quantity;
        $session->set('cart_jeux', $cart);

        $product = $entityManager->getRepository(Jeux::class)->find($id);
        $total = $product ? $product->getprixj() * $quantity : 0;

        return new JsonResponse(['total' => $total]);
    }
    #[Route('/cart-jeux', name: 'cart_jeux')]
    public function cart(SessionInterface $session, EntityManagerInterface $entityManager): Response
    {
        $cartRepo = $entityManager->getRepository(Cart::class);
        $cart = $cartRepo->findBy(['userId' => 0]);

        $jeuxRepository = $entityManager->getRepository(Jeux::class);
        $cartItems = [];

        foreach ($cart as $id => $quantity) {
            $jeux = $jeuxRepository->find($quantity->getEntityId());
            //dump($jeux);
            if ($jeux) {
                $cartItems[] = [
                    'jeux' => $jeux,
                    'quantity' => $quantity,
                    'total' => (int)$jeux->getprixj() * $quantity->getQuantity(),
                ];
            }
        }
            $totalPrix = 0;
        foreach ($cartItems as $id => $cartI){
            $totalPrix = $totalPrix + $cartI['total'];
        }
        return $this->render('cart/cart.jeux.html.twig', [
            'cartItems' => $cartItems,
            'cartTotalPrix' => $totalPrix,
        ]);
    }
    #[Route('/rate-jeux/{id}', name: 'rate_jeux')]
    public function rateJeux(Jeux $jeux, Request $request, EntityManagerInterface $entityManager, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        $submittedToken = $request->request->get('_token');

        if (!$csrfTokenManager->isTokenValid(new CsrfToken('token_id', $submittedToken))) {
            throw new \Exception('Invalid CSRF token');
        }

        $rating = $request->request->get('rating');

        if (!is_numeric($rating) || $rating < 1 || $rating > 5) {
            // Handle invalid rating
            // You might want to redirect back to the previous page with an error message
            return $this->redirectToRoute('previous_route_name');
        }


        $jeux->setTotalRating($jeux->getTotalRating() + 1);
        $jeux->setAverageRating(
            ($jeux->getAverageRating() * ($jeux->getTotalRating() - 1) + $rating) / $jeux->getTotalRating()
        );

        $entityManager->flush();


        return $this->redirectToRoute('product_details_jeux', ['id' => $jeux->getId()]);
    }








}
