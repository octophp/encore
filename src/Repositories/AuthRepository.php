<?php

namespace Octophp\Encore\Repositories;

use App\Entities\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository; 
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use DI\Attribute\Inject;
use Exception;

class AuthRepository extends EntityRepository
{
  /**
     * @var EntityRepository
     */
    private $repository;

    private $em;


    public function __construct(EntityManager $entityManager)
    {
        $this->repository = $entityManager->getRepository(User::class);
        $this->em = $entityManager;
    }

    public function findById(int $id)
    {
        return $this->repository->find($id);
    }  
 
    public function findAll()
    {
        return $this->repository->findBy(array());
    }    

    public function updateUser(User $user)
    {
        try {
            $this->em->persist($user);
            $this->em->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw new Exception($e);
        }
    }

    public function findByToken(string $token): ?User
    {
        return $this->repository->findOneBy(['token' => $token]);
    }

    public function findByEmail(string $email)
    {
        return $this->repository->findOneBy(array('email' => $email));
    }
}