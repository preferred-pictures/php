<?php

declare(strict_types=1);

namespace PreferredPictures;

/**
 * Implement a client for PreferredPictures that makes calling
 * the API methods easy and efficient.
 *
 * @property string $identity The identity used for API requests.
 * @property string $endpoint The endpoint for API requests made by this client.
 * @property int $max_choices The maximum number of choices allowed in each API call.
 *
 */
final class Client
{
    public $identity;
    private $secret_key;
    public $endpoint;
    public $max_choices;

    /**
     * Create a new client object
     *
     * @param string $identity The identity that should be used for the API calls.
     * @param string $secret_key The secret key of the identity that will be used to create request signatures
     * @param int $max_choices The maximum number of choices to allow in an API call
     * @param string $endpoint The endpoint of the API to use, default is https://api.preferred-pictures.com/
     *
     * @return Client A new PreferredPictures client object.
     */
    public function __construct(
        string $identity,
        string $secret_key,
        $max_choices = 35,
        $endpoint = 'https://api.preferred-pictures.com/'
    ) {
        $this->identity = $identity;
        $this->secret_key = $secret_key;

        $this->endpoint = $endpoint;
        $this->max_choices = $max_choices;
    }

    /**
     * Generate a random string of a specified length.
     *
     * @param int $length The length of the random string to produce
     *
     * @return string A random string
     */
    private function getRandomString(int $length)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-';
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }

        return $randomString;
    }

    /**
     * Build a URL for a call to /choose of the PreferredPictures API
     *
     * @param array $choices A list of choices of which a selection should be made
     * @param string $tournament The tournament of which this API call is a part.
     * @param int $ttl The amount of time in seconds after a choice is made that an action can be recorded.
     * @param int $expiration_ttl The amount of time in seconds that the request's signature is valid.
     * @param string $choices_prefix An optional prefix to prepend to all of the choices
     * @param string $choices_suffix An optional suffix to append to all of the choices
     * @param array $destinations A list of destination URLs which are paired with choices for redriection
     * @param string $destinations_prefix An optional prefix to prepend to all of the destination URLs
     * @param string $destinations_suffix An options suffix to append to all of the destination URLs
     *
     * @return string A URL that calls the PreferredPictures /choose API.
     */
    public function createChooseUrl(
        array $choices,
        string $tournament,
        int $ttl = 600,
        int $expiration_ttl = 3600,
        string $choices_prefix = "",
        string $choices_suffix = "",
        array $destinations = [],
        string $destinations_prefix = "",
        string $destinations_suffix = "",
        bool $go = false,
        bool $json = false
    ) {
        if (count($choices) > $this->max_choices) {
            throw new Exception("Too many choices presented to build URL");
        }

        $date = date_create();
        $request_params = [
            "choices" => $choices,
            "expiration" => date_timestamp_get($date) + $expiration_ttl,
            "tournament" => $tournament,
            "uid" => $this->getRandomString(30),
            "ttl" => $ttl,
        ];

        if ($choices_prefix != "") {
            $request_params['choices_prefix'] = $choices_prefix;
        }

        if ($choices_suffix != "") {
            $request_params['choices_suffix'] = $choices_suffix;
        }

        if (count($destinations) > 0) {
            $request_params['destinations'] = $destinations;
        }

        if ($destinations_prefix != "") {
            $request_params['destinations_prefix'] = $destinations_prefix;
        }

        if ($destinations_suffix != "") {
            $request_params['destinations_suffix'] = $destinations_suffix;
        }

        if ($go == true) {
            $request_params['go'] = 'true';
        }

        if ($json == true) {
            $request_params['json'] = 'true';
        }

        $signing_field_order = [
            "choices_prefix",
            "choices_suffix",
            "choices",
            "destinations_prefix",
            "destinations_suffix",
            "destinations",
            "expiration",
            "go",
            "json",
            "tournament",
            "ttl",
            "uid",
        ];

        $param_exists = function ($field_name) use ($request_params) {
            return array_key_exists($field_name, $request_params);
        };

        $param_lookup = function ($field_name) use ($request_params) {
            $value = $request_params[$field_name];
            if (is_array($value)) {
                return join(",", $value);
            }
            return $value;
        };

        $signing_string = join(
            "/",
            array_map(
                $param_lookup,
                array_filter(
                    $signing_field_order,
                    $param_exists
                )
            )
        );

        $signature = hash_hmac("sha256", $signing_string, $this->secret_key);


        $request_params['identity'] = $this->identity;
        $request_params['signature'] = $signature;

        $query_string = http_build_query($request_params);

        $query_string = preg_replace('/%5B[0-9]+%5D/simU', '%5B%5D', $query_string);
        return $this->endpoint . 'choose?' . $query_string;
    }
}
