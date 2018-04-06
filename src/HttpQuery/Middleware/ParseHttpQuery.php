<?php namespace Entrack\RestfulAPIService\HttpQuery\Middleware;

use Closure;
use Entrack\RestfulAPIService\HttpQuery\HttpQuery;
use Symfony\Component\HttpFoundation\Request;
use Entrack\RestfulAPIService\HttpQuery\HttpQueryParser;
use ReflectionClass;

class ParseHttpQuery {

    /**
     * Namespace for creating a new Reflection Instance
     *
     * @var string
     */
    protected $class = HttpQuery::class;

    /**
     * Object holder for reflection instance
     *
     * @var object $class
     */
    protected $instance;

    /**
     * Placeholder for request object
     *
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;


    /**
     * Handle the given request and get the response.
     *
     * @implements HttpKernelInterface::handle
     *
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * @param $type
     * @param bool $catch
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next)
    {
        $this->request = $request;
        $self = $this;
        if($request->isMethod('GET'))
        {
            // Set the singleton Instance within the IOC Container
            app()->singleton($this->class, function() use($self)
            {
                $instance =  $this->createHttpQueryInstance();
                $instance->setDefaultKey(HttpQueryParser::replaceWithUnderscores($this->request->segment(2)));

                $include = $this->getInclude();
                $instance->setIncludes($include);

                $has = $this->getHas();
                $instance->setHas($has);

                return $instance;
            });
        }

        // Pass down the response
        return $next($this->request);
    }

    public function getInclude()
    {
        $query = $this->request->query->all();

        if (array_key_exists('include', $query)) {
            $include = (array)$query['include'];
        } else {
            $include = [];
        }

        $include = HttpQueryParser::parse($include);
        $include = reset($include);

        return is_null($include) ? [] : $include;
    }

    public function getHas()
    {
        $has = $this->request->only('has') ?: [];
        $has = HttpQueryParser::parse($has);
//        $has = reset($has);

        return is_null($has) ? [] : $has;
    }

    /**
     * Creates a new Instance of $class and construct
     * params injected with Request params
     *
     * @return object
     * @throws \ReflectionException
     */
    public function createHttpQueryInstance()
    {
        // Create a new reflection instance of HttpQuery Object
        $this->setReflectionClassInstance();

        return $this->instance->newInstanceArgs(
            $this->getParsedRequestParams(
                $this->getInstanceParameterNames()
            )
        );
    }

    /**
     * Creates a new reflection class
     *
     * @return void
     * @throws \ReflectionException
     */
    protected function setReflectionClassInstance()
    {
        $this->instance = new ReflectionClass($this->class);
    }

    /**
     * Get's the default parameters/names from the instance class
     *
     * @return array
     */
    protected function getInstanceParameterNames()
    {
        $params = array_pluck(
            $this->instance->getConstructor()->getParameters(),
            'name'
        );

        $params[] = 'include';

        return $params;
    }

    /**
     * Get's parameters from the request by name
     *
     * @param $params
     * @return mixed
     */
    protected function getParsedRequestParams($params)
    {
        return HttpQueryParser::parse(
            $this->request->only($params)
        );
    }

}
