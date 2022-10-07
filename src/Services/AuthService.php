<?php

namespace Octo\Encore\Services;

use ReallySimpleJWT\Token;
use App\Entities\User;

class AuthService
{
    /**
     * @Inject
     * @var Psr\Container\ContainerInterface
     */
    public $container;  

    /**
     * @Inject
     * @var Psr\Log\LoggerInterface
     */
    public $logger;

    /**
     * @Inject
     * @var Octo\Encore\Repositories\AuthRepository
     */
    private $userRepository;

    public function verifyJwt(User $foundUser, string $token)
    {
        $accessSecret = $this->container->get('JWT_SECRET');
        $refreshSecret = $this->container->get('JWT_REFRESH_SECRET');
        $is_validated = Token::validate($token, $refreshSecret);
        if ($is_validated){
            $payload = Token::getPayload($token, $refreshSecret);
            if (!empty($payload) && isset($payload['user_id']) && $payload['user_id'] == $foundUser->getId()){
                $expiration = time() + 360;
                $issuer = 'localhost';
                $accessToken  = Token::create($foundUser->getId(), $accessSecret, $expiration, $issuer);
                return $accessToken;
            }
        }
        return false;
    }

    public function findByToken($token): ?User
    {
        return $this->userRepository->findByToken($token);
    }

    public function handleLogin(string $email, string $password)
    {
        $user = $this->userRepository->findByEmail($email);
        
        $user_password = $user->getPassword();
        
        if(password_verify($password, $user_password)) {
            $userId = $user->getId();
            $accessSecret = $this->container->get('JWT_SECRET');
            $refreshSecret = $this->container->get('JWT_REFRESH_SECRET');
            $accessTokenexpiration = time() + 10;
            $refreshTokenexpiration = time() + 360;
            $issuer = 'localhost';

            $user = $this->userRepository->find($userId);
            // return token
            $accessToken  = Token::create($userId, $accessSecret, $accessTokenexpiration, $issuer);
            // refresh token
            $refreshToken  = Token::create($userId, $refreshSecret, $refreshTokenexpiration, $issuer);

            $expires_at =  time() + (86400 * 30);
            setcookie('jwt',$refreshToken,$expires_at, '/', '', true, true);
            
            $user->setToken($refreshToken);
            $this->userRepository->updateUser($user);
            return $accessToken;
        }
    }

}