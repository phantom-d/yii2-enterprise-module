<?php
/**
 * @link https://github.com/phantom-d/yii2-enterprise-module
 * @copyright Copyright (c) 2018 Anton Ermolovich
 * @license http://opensource.org/licenses/MIT
 */

namespace enterprise\security;

use enterprise\helpers\ArrayHelper;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Claim\Factory as ClaimFactory;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Parsing\Decoder;
use Lcobucci\JWT\Parsing\Encoder;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Keychain;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\ValidationData;
use yii\base\InvalidParamException;

/**
 * JSON Web Token implementation, based on this library:
 * https://github.com/lcobucci/jwt
 *
 * Need to add some composer packages to root `composer.json`:
 *
 * ```json
 *
 * "require": {
 *     ...
 *     "lcobucci/jwt": "~3.1"
 * }
 *
 * ```
 *
 * If you want to use ECDSA (Elliptic Curve Digital Signature Algorithm), add to root `composer.json`
 *
 * ```json
 *
 * "require": {
 *     ...
 *     "mdanter/ecc" : "~0.5"
 * }
 *
 * ```
 *
 * @property \Lcobucci\JWT\Builder $builder Return [[Lcobucci\JWT\Builder::__construct()]] object
 * @property \Lcobucci\JWT\Parser $parser Return [[Lcobucci\JWT\Parser::__construct()]] object
 * @property \Lcobucci\JWT\ValidationData $validationData Return [[Lcobucci\JWT\ValidationData::__construct()]] object
 *
 * @author Dmitriy Demin <sizemail@gmail.com>
 * @author Anton Ermolovich <anton.ermolovich@gmail.com>
 * @since 1.0.0-a
 */
class Jwt extends \enterprise\Component
{
    /**
     * @var array Supported algorithms
     * @todo Add RSA, ECDSA suppport
     */
    public $supportedAlgs = [
        'HS256' => 'Lcobucci\JWT\Signer\Hmac\Sha256',
        'HS384' => 'Lcobucci\JWT\Signer\Hmac\Sha384',
        'HS512' => 'Lcobucci\JWT\Signer\Hmac\Sha512',
        'RS256' => 'Lcobucci\JWT\Signer\Rsa\Sha256',
        'RS384' => 'Lcobucci\JWT\Signer\Rsa\Sha384',
        'RS512' => 'Lcobucci\JWT\Signer\Rsa\Sha512',
        'ES256' => 'Lcobucci\JWT\Signer\Ecdsa\Sha256',
        'ES384' => 'Lcobucci\JWT\Signer\Ecdsa\Sha384',
        'ES512' => 'Lcobucci\JWT\Signer\Ecdsa\Sha512',
    ];

    /**
     * @var string|null Algorithm for encoding
     */
    public $algorithm;

    /**
     * @var string|array|null $key The key, or map of keys.
     */
    public $key;

    /**
     * @var string|null The passphrase for key
     */
    public $passphrase;

    /**
     * @var integer Token lifetime
     */
    public $tokenLifetime = 3600;

    /**
     * @var array
     */
    private static $_keys;

    /**
     * {@inheritdoc}
     * @throws \yii\base\InvalidParamException
     */
    public function init()
    {
        parent::init();
        if (empty($this->key)) {
            $message = 'Missing required parameters: {params}';
            throw new InvalidParamException(\Yii::t('yii', $message, ['params' => 'key']));
        }
    }

    /**
     * Return keys
     *
     * @param \Lcobucci\JWT\Signer $signer
     *
     * @throws \yii\base\InvalidParamException
     * @return \Lcobucci\JWT\Signer\Key[]
     */
    public function getKeys(Signer $signer)
    {
        $algorithm = $signer->getAlgorithmId();
        $return = null;

        if (empty(static::$_keys[$algorithm])) {
            $keys = [
                'public'  => null,
                'private' => null,
            ];
            if (is_string($this->key)) {
                $keys = [
                    'public'  => $this->key,
                    'private' => $this->key,
                ];
            } elseif (is_array($this->key)) {
                if (ArrayHelper::isAssociative($this->key)) {
                    if (isset($this->key['public'])) {
                        $keys['public'] = $this->key['public'];
                    }
                    if (isset($this->key['private'])) {
                        $keys['private'] = $this->key['private'];
                    }
                } else {
                    $values = array_values($this->key);
                    $keys = [
                        'public'  => $values[0],
                        'private' => $values[1] ?? null,
                    ];
                }
            }

            array_walk($keys, function (&$value, $key) {
                if ($value && $return = \Yii::getAlias($value)) {
                    $value = $return;
                }
            });

            if ($keys['public']) {
                $keychain = new Keychain();
                $public = is_file($keys['public']) ? 'file://' . $keys['public'] : $keys['public'];
                $key = $keychain->getPublicKey($public);
                $return = [
                    'public'  => $key,
                    'private' => $key,
                ];
                if ($keys['private']) {
                    $private = is_file($keys['private']) ? 'file://' . $keys['private'] : $keys['private'];
                    $return['private'] = $keychain->getPrivateKey($private, $this->passphrase);
                }
            }

            if (empty($return)) {
                throw new InvalidParamException('Invalid security key!');
            }
            static::$_keys[$algorithm] = $return;
        }
        return static::$_keys[$algorithm];
    }

    /**
     * @see [[Lcobucci\JWT\Builder::__construct()]]
     * @return \Lcobucci\JWT\Builder
     */
    public function getBuilder(Encoder $encoder = null, ClaimFactory $claimFactory = null)
    {
        return new Builder($encoder, $claimFactory);
    }

    /**
     * @see [[Lcobucci\JWT\Parser::__construct()]]
     * @return \Lcobucci\JWT\Parser
     */
    public function getParser(Decoder $decoder = null, ClaimFactory $claimFactory = null)
    {
        return new Parser($decoder, $claimFactory);
    }

    /**
     * @see [[Lcobucci\JWT\ValidationData::__construct()]]
     * @param null|mixed $currentTime
     * @return \Lcobucci\JWT\ValidationData
     */
    public function getValidationData($currentTime = null)
    {
        return new ValidationData($currentTime);
    }

    /**
     * Return Signer of algorithm
     *
     * @param string $algorithm
     * @throws \InvalidArgumentException
     * @return \Lcobucci\JWT\Signer
     */
    public function getSigner($algorithm)
    {
        if (empty($this->supportedAlgs[$algorithm])) {
            throw new \InvalidArgumentException('Algorithm not supported');
        }

        return \Yii::createObject($this->supportedAlgs[$algorithm]);
    }

    /**
     * Return token
     *
     * @param array $params
     * @return string
     */
    public function buildToken($params = [])
    {
        $builder = $this->getBuilder();
        if ($sessionId = session_id()) {
            $params = ArrayHelper::merge((array)$params, ['session_id' => $sessionId]);
        }

        $token = $builder->expiresAt(time() + $this->tokenLifetime);

        foreach ($params as $key => $value) {
            $token->with($key, $value);
        }

        $signer = $this->getSigner($this->algorithm);
        $keys = $this->getKeys($signer);

        return $token->sign($signer, $keys['private'])->getToken();
    }

    /**
     * Parses the JWT and returns a token class
     *
     * @param string $token JWT
     * @param bool $validate
     * @param bool $verify
     * @return \Lcobucci\JWT\Token|null
     */
    public function loadToken($token, $validate = true, $verify = true)
    {
        try {
            $token = $this->getParser()->parse((string)$token);
        } catch (\RuntimeException $e) {
            \Yii::warning('Invalid JWT provided: ' . $e->getMessage(), __METHOD__);
            return null;
        } catch (\InvalidArgumentException $e) {
            \Yii::warning('Invalid JWT provided: ' . $e->getMessage(), __METHOD__);
            return null;
        }

        if ($validate && !$this->validateToken($token)) {
            return null;
        }

        if ($verify && !$this->verifyToken($token)) {
            return null;
        }

        return $token;
    }

    /**
     * Validate token
     *
     * @param \Lcobucci\JWT\Token $token token object
     * @param null|mixed $currentTime
     * @return bool
     */
    public function validateToken(Token $token, $currentTime = null)
    {
        $data = $this->getValidationData($currentTime);
        // @todo Add claims for validation

        return $token->validate($data);
    }

    /**
     * Verify token
     *
     * @param \Lcobucci\JWT\Token $token token object
     * @return bool
     */
    public function verifyToken(Token $token)
    {
        $alg = $token->getHeader('alg');

        $signer = $this->getSigner($alg);
        $keys = $this->getKeys($signer);

        return $token->verify($signer, $keys['public']);
    }
}
