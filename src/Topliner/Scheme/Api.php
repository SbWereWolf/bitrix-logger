<?php


namespace Topliner\Scheme;


use LanguageSpecific\ArrayHandler;

class Api
{
    /**
     * @var ArrayHandler
     */
    private $parameters;

    public function __construct(array $body)
    {
        $this->parameters = new ArrayHandler($body);
    }

    public function run()
    {
        $output = ['success' => false, 'message' => 'Call not found'];

        $call = $this->parameters->get('call')->str();
        $isValid = false;
        $isFound = false;
        switch ($call) {
            case'new':
                $isFound = true;
                $isValid = $this->parameters->has('x')
                    && $this->parameters->has('y')
                    && $this->parameters->has('type');
                break;
            case'store':
                $isFound = true;
                $isValid = $this->parameters->has('x')
                    && $this->parameters->has('y')
                    && $this->parameters->has('number');
                break;
            case'publish':
                $isFound = true;
                $isValid = $this->parameters->has('number');
                break;
            case'flush':
            case'reset':
                $isFound = true;
                $isValid = true;
                break;
        }
        if ($isValid) {
            $output = (new Landmark($this->parameters))->process();
        }
        if (!$isValid && $isFound) {
            $output['message'] = 'Parameters of call is not valid';
        }

        return $output;
    }
}