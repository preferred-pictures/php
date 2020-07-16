<?php

declare(strict_types=1);

/**
 * Implement a client for Preferred.pictures that makes calling
 * the API methods easy and efficient.
 *
 */
final class Client
{
    private $identity;
    private $secret_key;
    public $endpoint;
    public $max_choices;

    /**
     * Create a new client object
     *
     * @param string $identity The identity that should be used for the API calls.
     * @param string $secret_key The secret key of the identity that will be used to create request signatures
     * @param int $max_choices The maximum number of choices to allow in an API call
     * @param string $endpoint The endpoint of the API to use, default is https://api.preferred.pictures/
     *
     * @return Client A new Preferred.pictures client object.
     */
    public function __construct(
        string $identity,
        string $secret_key,
        $max_choices = 35,
        $endpoint = 'https://api.preferred.pictures/'
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
     * Build a URL for a call to /choose-url of the Preferred.pictures API
     *
     * @param array $choices A list of chocies of which a selection should be made
     * @param string $tournament The tournament of which this API call is a part.
     * @param int $ttl The amount of time in seconds after a choice is made that an action can be recorded.
     * @param int $expiration_ttl The amount of time in seconds that the request's signature is valid.
     * @param string $prefix An optional prefix to prepend to all of the choices
     * @param string $suffix An optional suffix to append to all of the choices
     *
     * @return string A URL that calls the Preferred.pictures /choose-url API.
     */
    public function createChooseUrl(
        array $choices,
        string $tournament,
        int $ttl = 600,
        int $expiration_ttl = 3600,
        string $prefix = "",
        string $suffix = ""
    ) {
        if (count($choices) > $this->max_choices) {
            throw new Exception("Too many choices presented to build URL");
        }

        $date = date_create();
        $request_params = [
            "choices" => join(",", $choices),
            "expiration" => date_timestamp_get($date) + $expiration_ttl,
            "tournament" => $tournament,
            "uid" => $this->getRandomString(30),
            "ttl" => $ttl,
        ];

        if ($prefix != "") {
            $request_params['prefix'] = $prefix;
        }
        if ($suffix != "") {
            $request_params['suffix'] = $suffix;
        }

        $signing_field_order = [
            "choices",
            "expiration",
            "prefix",
            "suffix",
            "tournament",
            "ttl",
            "uid",
        ];

        $param_exists = function ($field_name) use ($request_params) {
            return array_key_exists($field_name, $request_params);
        };

        $param_lookup = function ($field_name) use ($request_params) {
            return $request_params[$field_name];
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

        return $this->endpoint . 'choose-url?' . $query_string;
    }
}
