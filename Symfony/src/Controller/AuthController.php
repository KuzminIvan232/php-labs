<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

/**
 * Lab 5: registration + "who am I" for the JWT auth system.
 */
class AuthController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * This body is never actually executed. Symfony's Router runs BEFORE the
     * firewall layer on every request, so a route still has to exist here or
     * POST /auth/login 404s before the `login` firewall's json_login listener
     * (config/packages/security.yaml) ever gets a chance to intercept it,
     * check the credentials against app_user_provider, and hand off to
     * Lexik's success handler to issue the JWT.
     */
    #[Route('/auth/login', name: 'auth_login', methods: [Request::METHOD_POST])]
    public function login(): JsonResponse
    {
        throw new \LogicException('This should never be reached: the "login" firewall should have intercepted the request first.');
    }

    /**
     * Public self-registration. Always creates a ROLE_CLIENT account —
     * Manager/Admin accounts are provisioned by an Admin via
     * PUT /users/{id}/role (see UserController), never by public signup,
     * so nobody can grant themselves elevated access.
     */
    #[Route('/auth/register', name: 'auth_register', methods: [Request::METHOD_POST])]
    public function register(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $requestData = json_decode($request->getContent(), associative: true);

        $email = $requestData['email'] ?? null;
        $password = $requestData['password'] ?? null;

        if (!$email || !$password) {
            return new JsonResponse(['data' => ['error' => 'email and password are required']], status: Response::HTTP_BAD_REQUEST);
        }

        if ($userRepository->findOneBy(['email' => $email])) {
            return new JsonResponse(['data' => ['error' => 'A user with this email already exists']], status: Response::HTTP_CONFLICT);
        }

        $user = new User();
        $user->setEmail($email);
        $user->setRole(User::ROLE_CLIENT);
        $user->setPassword($passwordHasher->hashPassword($user, $password));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse(['data' => $user->toArray()], status: Response::HTTP_CREATED);
    }

    /**
     * Returns the authenticated user's own account — handy for checking which
     * role a token carries.
     */
    #[Route('/auth/me', name: 'auth_me', methods: [Request::METHOD_GET])]
    public function me(#[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return new JsonResponse(['data' => ['error' => 'Not authenticated']], status: Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse(['data' => $user->toArray()], status: Response::HTTP_OK);
    }
}
