<?php

namespace App\Traits;

use DateTime;
use App\Models\User;
use DateTimeImmutable;
use GuzzleHttp\Psr7\Response;
use Laravel\Passport\Passport;
use Illuminate\Events\Dispatcher;
use League\OAuth2\Server\CryptKey;
use Laravel\Passport\Bridge\Client;
use Laravel\Passport\TokenRepository;
use Laravel\Passport\Bridge\AccessToken;
use Laravel\Passport\Bridge\AccessTokenRepository;
use Laravel\Passport\Bridge\RefreshTokenRepository;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResponseTypes\BearerTokenResponse;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;

# https://github.com/laravel/passport/issues/71
//$user = User::find(1);
//// return  response
//return $this->getBearerTokenByUser($user, 1, true);
//// return array
//return $this->getBearerTokenByUser($user, 1, false);

trait PassportToken
{
    private function generateUniqueIdentifier($length = 40)
    {
        try {
            return bin2hex(random_bytes($length));
            // @codeCoverageIgnoreStart
        } catch (\TypeError $e) {
            throw OAuthServerException::serverError('An unexpected error has occurred');
        } catch (\Error $e) {
            throw OAuthServerException::serverError('An unexpected error has occurred');
        } catch (\Exception $e) {
            // If you get this message, the CSPRNG failed hard.
            throw OAuthServerException::serverError('Could not generate a random string');
        }
        // @codeCoverageIgnoreEnd
    }

    private function issueRefreshToken(AccessTokenEntityInterface $accessToken)
    {
        $maxGenerationAttempts = 10;
        $refreshTokenRepository = app(RefreshTokenRepository::class);

        $refreshToken = $refreshTokenRepository->getNewRefreshToken();
//        $refreshToken->setExpiryDateTime((new \DateTime())->add(Passport::refreshTokensExpireIn()));
        $refreshToken->setExpiryDateTime((new DateTimeImmutable())->add(Passport::refreshTokensExpireIn()));
        $refreshToken->setAccessToken($accessToken);

        while ($maxGenerationAttempts-- > 0) {
            $refreshToken->setIdentifier($this->generateUniqueIdentifier());
            try {
                $refreshTokenRepository->persistNewRefreshToken($refreshToken);

                return $refreshToken;
            } catch (UniqueTokenIdentifierConstraintViolationException $e) {
                if ($maxGenerationAttempts === 0) {
                    throw $e;
                }
            }
        }
    }

    protected function createPassportTokenByUser(User $user, $clientId)
    {
//        $accessToken = new AccessToken($user->id);
        $accessToken = new AccessToken($user->id,[],new Client($clientId, null, null));
        $accessToken->setIdentifier($this->generateUniqueIdentifier());

//        $accessToken->setExpiryDateTime((new DateTime())->add(Passport::tokensExpireIn()));
        $accessToken->setExpiryDateTime((new DateTimeImmutable())->add(Passport::tokensExpireIn()) );

        $accessTokenRepository = new AccessTokenRepository(new TokenRepository(), new Dispatcher());
        $accessTokenRepository->persistNewAccessToken($accessToken);
        $refreshToken = $this->issueRefreshToken($accessToken);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
        ];
    }

    protected function sendBearerTokenResponse(AccessTokenEntityInterface $accessToken, $refreshToken)
    {
        $response = new BearerTokenResponse();
        $response->setAccessToken($accessToken);
        $response->setRefreshToken($refreshToken);

        if (config('passport.private_key')) {
            $privateKey = new CryptKey(config('passport.private_key'), null, false);
        } else {
            $privateKey = new CryptKey('file://' . Passport::keyPath('oauth-private.key'), null, false);
        }

        $accessToken->SetprivateKey($privateKey);
        $response->setPrivateKey($privateKey);
        $response->setEncryptionKey(app('encrypter')->getKey());

        return $response->generateHttpResponse(new Response);
    }

    protected function getBearerTokenByUser(User $user, $clientId, $output = true)
    {
        $passportToken = $this->createPassportTokenByUser($user, $clientId);
        $bearerToken = $this->sendBearerTokenResponse($passportToken['access_token'], $passportToken['refresh_token']);

        if (! $output) {
            $bearerToken = json_decode($bearerToken->getBody()->__toString(), true);
        }

        return $bearerToken;
    }
}
