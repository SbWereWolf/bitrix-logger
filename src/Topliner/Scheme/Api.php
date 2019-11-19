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
        switch ($call) {
            case'new':
                $isValid = $this->parameters->has('x')
                    && $this->parameters->has('y')
                    && $this->parameters->has('type');
                break;
            case'store':
                $isValid = $this->parameters->has('x')
                    && $this->parameters->has('y')
                    && $this->parameters->has('number');
                break;
            case'publish':
                $isValid = $this->parameters->has('number');
                break;
        }
        if ($isValid) {
            $output = (new Landmark($this->parameters))->process();
        }


        return $output;
    }
}