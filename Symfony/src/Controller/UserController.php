<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Lab 5: account/role administration — Admin only. Accounts themselves are
 * created via the public POST /auth/register (always as ROLE_CLIENT); this
 * controller is how an Admin promotes someone to Manager/Admin afterwards,
 * so nobody can grant themselves elevated access through registration.
 */
#[IsGranted('ROLE_ADMIN')]
class UserController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('/users', name: 'get_users', methods: [Request::METHOD_GET])]
    public function getUsers(UserRepository $userRepository): JsonResponse
    {
        $users = array_map(
            static fn (User $user) => $user->toArray(),
            $userRepository->findAll()
        );

        return new JsonResponse(['data' => $users], status: Response::HTTP_OK);
    }

    #[Route('/users/{id}', name: 'get_user_item', methods: [Request::METHOD_GET])]
    public function getUserItem(int $id, UserRepository $userRepository): JsonResponse
    {
        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse(['data' => ['error' => 'Not found user by id ' . $id]], status: Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['data' => $user->toArray()], status: Response::HTTP_OK);
    }

    /**
     * Changes a user's role, e.g. {"role": "manager"}.
     */
    #[Route('/users/{id}/role', name: 'update_user_role', methods: [Request::METHOD_PUT, Request::METHOD_PATCH])]
    public function updateUserRole(int $id, Request $request, UserRepository $userRepository): JsonResponse
    {
        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse(['data' => ['error' => 'Not found user by id ' . $id]], status: Response::HTTP_NOT_FOUND);
        }

        $requestData = json_decode($request->getContent(), associative: true);
        $role = $requestData['role'] ?? null;

        if (!in_array($role, User::ROLES, true)) {
            return new JsonResponse(['data' => ['error' => 'role must be one of: ' . implode(', ', User::ROLES)]], status: Response::HTTP_BAD_REQUEST);
        }

        $user->setRole($role);
        $this->entityManager->flush();

        return new JsonResponse(['data' => $user->toArray()], status: Response::HTTP_OK);
    }

    #[Route('/users/{id}', name: 'delete_user', methods: [Request::METHOD_DELETE])]
    public function deleteUser(int $id, UserRepository $userRepository): JsonResponse
    {
        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse(['data' => ['error' => 'Not found user by id ' . $id]], status: Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return new JsonResponse(['data' => ['message' => 'User ' . $id . ' deleted']], status: Response::HTTP_OK);
    }
}
