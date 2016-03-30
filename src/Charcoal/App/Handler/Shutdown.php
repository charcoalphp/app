<?php

namespace Charcoal\App\Handler;

// Dependencies from PSR-7 (HTTP Messaging)
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;

// Dependency from Slim
use \Slim\Http\Body;

// Dependency from 'charcoal-translation'
use \Charcoal\Translation\Catalog\CatalogInterface;

// Local Dependencies
use \Charcoal\App\Handler\AbstractHandler;

/**
 * Shutdown Handler
 *
 * It outputs a simple message in either JSON, XML, or HTML based on the Accept header.
 *
 * A maintenance mode check is included in the default middleware stack for your application.
 * This is a practical feature to "disable" your application while performing an update
 * or maintenance.
 */
class Shutdown extends AbstractHandler
{
    /**
     * Invoke "Maintenance" Handler
     *
     * @param  ServerRequestInterface $request  The most recent Request object.
     * @param  ResponseInterface      $response The most recent Response object.
     * @param  string[]               $methods  Allowed HTTP methods.
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $methods)
    {
        $this->setMethods($methods);

        if ($request->getMethod() === 'OPTIONS') {
            $contentType = 'text/plain';
            $output = $this->renderPlainOutput();
        } else {
            $contentType = $this->determineContentType($request);
            switch ($contentType) {
                case 'application/json':
                    $output = $this->renderJsonOutput();
                    break;

                case 'text/xml':
                case 'application/xml':
                    $output = $this->renderXmlOutput();
                    break;

                case 'text/html':
                default:
                    $output = $this->renderHtmlOutput();
                    break;
            }
        }

        $body = new Body(fopen('php://temp', 'r+'));
        $body->write($output);

        return $response
                ->withStatus(503)
                ->withHeader('Content-type', $contentType)
                ->withBody($body);
    }

    /**
     * Set the HTTP methods allowed by the current request.
     *
     * @param  array $methods Case-sensitive array of methods.
     * @return Shutdown Chainable
     */
    protected function setMethods(array $methods)
    {
        $this->methods = implode(', ', $methods);

        return $this;
    }

    /**
     * Retrieves the HTTP methods allowed by the current request.
     *
     * @return string Returns the allowed request methods.
     */
    public function methods()
    {
        return $this->methods;
    }

    /**
     * Render Plain/Text Error
     *
     * @return string
     */
    protected function renderPlainOutput()
    {
        $message = $this->catalog()->translate('down-for-maintenance');

        return $this->render($message);
    }

    /**
     * Render JSON Error
     *
     * @return string
     */
    protected function renderJsonOutput()
    {
        $message = $this->catalog()->translate('currently-unavailable');

        return $this->render('{"message":"'.$message.'"}');
    }

    /**
     * Render XML Error
     *
     * @return string
     */
    protected function renderXmlOutput()
    {
        $message = $this->catalog()->translate('currently-unavailable');

        return $this->render('<root><message>'.$message.'</message></root>');
    }

    /**
     * Render title of error
     *
     * @return string
     */
    public function messageTitle()
    {
        return $this->catalog()->entry('down-for-maintenance');
    }

    /**
     * Render body of HTML error
     *
     * @return string
     */
    public function renderHtmlMessage()
    {
        $title   = $this->messageTitle();
        $notice  = $this->catalog()->entry('currently-unavailable');
        $message = '<h1>'.$title."</h1>\n\t\t<p>".$notice."</p>\n";

        return $message;
    }

    /**
     * Sets a translation catalog instance on the object.
     *
     * @param  CatalogInterface $catalog A translation catalog object.
     * @return Shutdown Chainable
     */
    public function setCatalog(CatalogInterface $catalog)
    {
        parent::setCatalog($catalog);

        $messages = [
            'down-for-maintenance' => [
                'en' => 'Down for maintenance!',
                'fr' => 'En maintenance!',
                'es' => '¡Fuera de servicio por mantenimiento!'
            ],
            'currently-unavailable' => [
                'en' => 'We are currently unavailable. Check back in 15 minutes.',
                'fr' => 'Nous sommes actuellement indisponible. Revenez en 15 minutes.',
                'es' => 'Estamos actualmente fuera de servicio. Vuelve en 15 minutos.'
            ]
        ];

        foreach ($messages as $key => $entry) {
            if (!$this->catalog()->hasEntry($key)) {
                $this->catalog()->addEntry($key, $entry);
            }
        }

        return $this;
    }
}