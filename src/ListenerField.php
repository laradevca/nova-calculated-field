<?php

namespace Gldrenthe89\NovaCalculatedField;

use Gldrenthe89\NovaCalculatedField\Traits\CanDisableCalculationOnUpdateTrait;
use Illuminate\Http\Request;
use Laravel\Nova\Element;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;

class ListenerField extends Field
{
    use CanDisableCalculationOnUpdateTrait;

    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'listener-field';

    /**
     * The event this fields listens for
     * @var array|string
     */
    protected $listensTo;

    /**
     * @var boolean calculation button visible
     * Default to true
     */
    protected $buttonVisible;

    /**
     * The function to call when input is detected
     * @var Closure
     */
    public $calculateFunction;

    /**
     * If request is an update of update attached request
     *
     * @var Closure
     */
    public $isUpdating;

    /***
     * ListenerField constructor.
     * @param $name
     * @param null $attribute
     * @param callable|null $resolveCallback
     */
    public function __construct($name, $attribute = null, callable $resolveCallback = null)
    {
        parent::__construct($name, $attribute, $resolveCallback);

        $this->listensTo = 'broadcast-field-input';

        $this->buttonVisible = true;

        $this->isUpdating = app(NovaRequest::class)->isUpdateOrUpdateAttachedRequest();

        $this->calculateFunction = static function ($values, Request $request) {
            return collect($values)->values()->sum();
        };
    }

    /**
     * The channel that the client side component listens to
     * @param array|string $channel
     * @return $this
     */
    public function listensTo($channel) {
        $this->listensTo = $channel;
        return $this;
    }

    public function showCalculationButton($state = true) {
        $this->buttonVisible = $state;
        return $this;
    }

    /**
     * Tells the client side component which channel to broadcast on
     * @param array|string $broadcastChannel
     * @return Element
     */
    public function broadcastTo($broadcastChannel) : Element
    {
        return $this->withMeta([
            'broadcastTo' => $broadcastChannel
        ]);
    }

    /***
     * The callback we want to call when the field has some input
     *
     * @param callable $calculateFunction
     * @return $this
     */
    public function calculateWith(callable $calculateFunction) {
        $this->calculateFunction = $calculateFunction;
        return $this;
    }

    /**
     * Allows us to set the step attribute on the input broadcaster field
     * @param $broadcastChannel
     * @return Element
     */
    public function setStep($value) : Element
    {
        return $this->withMeta([
            'step' => $value
        ]);
    }

    /***
     * Serialize the field to JSON
     * @return array
     */
    public function jsonSerialize() : array
    {
        return array_merge([
            'isUpdating' => $this->isUpdating,
            'listensTo' => $this->listensTo,
            'buttonVisible' => $this->buttonVisible ?? true,
            'step'  => 'any'
        ], parent::jsonSerialize());
    }
}
