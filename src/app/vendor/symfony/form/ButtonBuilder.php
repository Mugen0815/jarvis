<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Exception\BadMethodCallException;
use Symfony\Component\Form\Exception\InvalidArgumentException;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

/**
 * A builder for {@link Button} instances.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 *
 * @implements \IteratorAggregate<string, FormBuilderInterface>
 */
class ButtonBuilder implements \IteratorAggregate, FormBuilderInterface
{
    protected bool $locked = false;

    private bool $disabled = false;
    private ResolvedFormTypeInterface $type;
    private string $name;
    private array $attributes = [];
    private array $options;

    /**
     * @throws InvalidArgumentException if the name is empty
     */
    public function __construct(?string $name, array $options = [])
    {
        if ('' === $name || null === $name) {
            throw new InvalidArgumentException('Buttons cannot have empty names.');
        }

        $this->name = $name;
        $this->options = $options;

        FormConfigBuilder::validateName($name);
    }

    /**
     * Unsupported method.
     *
     * @throws BadMethodCallException
     */
    public function add(string|FormBuilderInterface $child, string $type = null, array $options = []): never
    {
        throw new BadMethodCallException('Buttons cannot have children.');
    }

    /**
     * Unsupported method.
     *
     * @throws BadMethodCallException
     */
    public function create(string $name, string $type = null, array $options = []): never
    {
        throw new BadMethodCallException('Buttons cannot have children.');
    }

    /**
     * Unsupported method.
     *
     * @throws BadMethodCallException
     */
    public function get(string $name): never
    {
        throw new BadMethodCallException('Buttons cannot have children.');
    }

    /**
     * Unsupported method.
     *
     * @throws BadMethodCallException
     */
    public function remove(string $name): never
    {
        throw new BadMethodCallException('Buttons cannot have children.');
    }

    /**
     * Unsupported method.
     */
    public function has(string $name): bool
    {
        return false;
    }

    /**
     * Returns the children.
     */
    public function all(): array
    {
        return [];
    }

    /**
     * Creates the button.
     */
    public function getForm(): Button
    {
        return new Button($this->getFormConfig());
    }

    /**
     * Unsupported method.
     *
     * @throws BadMethodCallException
     */
    public function addEventListener(string $eventName, callable $listener, int $priority = 0): never
    {
        throw new BadMethodCallException('Buttons do not support event listeners.');
    }

    /**
     * Unsupported method.
     *
     * @throws BadMethodCallException
     */
    public function addEventSubscriber(EventSubscriberInterface $subscriber): never
    {
        throw new BadMethodCallException('Buttons do not support event subscribers.');
    }

    /**
     * Unsupported method.
     *
     * @throws BadMethodCallException
     */
    public function addViewTransformer(DataTransformerInterface $viewTransformer, bool $forcePrepend = false): never
    {
        throw new BadMethodCallException('Buttons do not support data transformers.');
    }

    /**
     * Unsupported method.
     *
     * @throws BadMethodCallException
     */
    public function resetViewTransformers(): never
    {
        throw new BadMethodCallException('Buttons do not support data transformers.');
    }

    /**
     * Unsupported method.
     *
     * @throws BadMethodCallException
     */
    public function addModelTransformer(DataTransformerInterface $modelTransformer, bool $forceAppend = false): never
    {
        throw new BadMethodCallException('Buttons do not support data transformers.');
    }

    /**
     * Unsupported method.
     *
     * @throws BadMethodCallException
     */
    public function resetModelTransformers(): never
    {
        throw new BadMethodCallException('Buttons do not support data transformers.');
    }

    /**
     * @return $this
     */
    public function setAttribute(string $name, mixed $value): static
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function setAttributes(array $attributes): static
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Unsupported method.
     *
     * @throws BadMethodCallException
     */
    public function setDataMapper(?DataMapperInterface $dataMapper): never
    {
        throw new BadMethodCallException('Buttons do not support data mappers.');
    }

    /**
     * Set whether the button is disabled.
     *
     * @return $this
     */
    public function setDisabled(bool $disabled): static
    {
        $this->disabled = $disabled;

        return $this;
    }

    /**
     * Unsupported method.
     *
     * @throws BadMethodCallException
     */
    public function setEmptyData(mixed $emptyData): never
    {
        throw new BadMethodCallException('Buttons do not support empty data.');
    }

    /**
     * Unsupported method.
     *
     * @throws BadMethodCallException
     */
    public function setErrorBubbling(bool $errorBubbling): never
    {
        throw new BadMethodCallException('Buttons do not support error bubbling.');
    }

    /**
     * Unsupported method.
     *
     * @throws BadMethodCallException
     */
    public function setRequired(bool $required): never
    {
        throw new BadMethodCallException('Buttons cannot be required.');
    }

    /**
     * Unsupported method.
     *
     * @throws BadMethodCallException
     */
    public function setPropertyPath(string|PropertyPathInterface|null $propertyPath): never
    {
        throw new BadMethodCallException('Buttons do not support property paths.');
    }

    /**
     * Unsupported method.
     *
     * @throws BadMethodCallException
     */
    public function setMapped(bool $mapped): never
    {
        throw new BadMethodCallException('Buttons do not support data mapping.');
    }

    /**
     * Unsupported method.
     *
     * @throws BadMethodCallException
     */
    public function setByReference(bool $byReference): never
    {
        throw new BadMethodCallException('Buttons do not support data mapping.');
    }

    /**
     * Unsupported method.
     *
     * @throws BadMethodCallException
     */
    public function setCompound(bool $compound): never
    {
        throw new BadMethodCallException('Buttons cannot be compound.');
    }

    /**
     * Sets the type of the button.
     *
     * @return $this
     */
    public function setType(ResolvedFormTypeInterface $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Unsupported method.
     *
     * @throws BadMethodCallException
     */
    public function setData(mixed $data): never
    {
        throw new BadMethodCallException('Buttons do not support data.');
    }

    /**
     * Unsupported method.
     *
     * @throws BadMethodCallException
     */
    public function setDataLocked(bool $locked): never
    {
        throw new BadMethodCallException('Buttons do not support data locking.');
    }

    /**
     * Unsupported method.
     *
     * @throws BadMethodCallException
     */
    public function setFormFactory(FormFactoryInterface $formFactory): never
    {
        throw new BadMethodCallException('Buttons do not support form factories.');
    }

    /**
     * Unsupported method.
     *
     * @throws BadMethodCallException
     */
    public function setAction(string $action): never
    {
        throw new BadMethodCallException('Buttons do not support actions.');
    }

    /**
     * Unsupported method.
     *
     * @throws BadMethodCallException
     */
    public function setMethod(string $method): never
    {
        throw new BadMethodCallException('Buttons do not support methods.');
    }

    /**
     * Unsupported method.
     *
     * @throws BadMethodCallException
     */
    public function setRequestHandler(RequestHandlerInterface $requestHandler): never
    {
        throw new BadMethodCallException('Buttons do not support request handlers.');
    }

    /**
     * Unsupported method.
     *
     * @return $this
     *
     * @throws BadMethodCallException
     */
    public function setAutoInitialize(bool $initialize): static
    {
        if (true === $initialize) {
            throw new BadMethodCallException('Buttons do not support automatic initialization.');
        }

        return $this;
    }

    /**
     * Unsupported method.
     *
     * @throws BadMethodCallException
     */
    public function setInheritData(bool $inheritData): never
    {
        throw new BadMethodCallException('Buttons do not support data inheritance.');
    }

    /**
     * Builds and returns the button configuration.
     */
    public function getFormConfig(): FormConfigInterface
    {
        // This method should be idempotent, so clone the builder
        $config = clone $this;
        $config->locked = true;

        return $config;
    }

    /**
     * Unsupported method.
     *
     * @throws BadMethodCallException
     */
    public function setIsEmptyCallback(?callable $isEmptyCallback): never
    {
        throw new BadMethodCallException('Buttons do not support "is empty" callback.');
    }

    /**
     * Unsupported method.
     *
     * @throws BadMethodCallException
     */
    public function getEventDispatcher(): never
    {
        throw new BadMethodCallException('Buttons do not support event dispatching.');
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Unsupported method.
     */
    public function getPropertyPath(): ?PropertyPathInterface
    {
        return null;
    }

    /**
     * Unsupported method.
     */
    public function getMapped(): bool
    {
        return false;
    }

    /**
     * Unsupported method.
     */
    public function getByReference(): bool
    {
        return false;
    }

    /**
     * Unsupported method.
     */
    public function getCompound(): bool
    {
        return false;
    }

    /**
     * Returns the form type used to construct the button.
     */
    public function getType(): ResolvedFormTypeInterface
    {
        return $this->type;
    }

    /**
     * Unsupported method.
     */
    public function getViewTransformers(): array
    {
        return [];
    }

    /**
     * Unsupported method.
     */
    public function getModelTransformers(): array
    {
        return [];
    }

    /**
     * Unsupported method.
     */
    public function getDataMapper(): ?DataMapperInterface
    {
        return null;
    }

    /**
     * Unsupported method.
     */
    public function getRequired(): bool
    {
        return false;
    }

    /**
     * Returns whether the button is disabled.
     */
    public function getDisabled(): bool
    {
        return $this->disabled;
    }

    /**
     * Unsupported method.
     */
    public function getErrorBubbling(): bool
    {
        return false;
    }

    /**
     * Unsupported method.
     */
    public function getEmptyData(): mixed
    {
        return null;
    }

    /**
     * Returns additional attributes of the button.
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Returns whether the attribute with the given name exists.
     */
    public function hasAttribute(string $name): bool
    {
        return \array_key_exists($name, $this->attributes);
    }

    /**
     * Returns the value of the given attribute.
     */
    public function getAttribute(string $name, mixed $default = null): mixed
    {
        return \array_key_exists($name, $this->attributes) ? $this->attributes[$name] : $default;
    }

    /**
     * Unsupported method.
     */
    public function getData(): mixed
    {
        return null;
    }

    /**
     * Unsupported method.
     */
    public function getDataClass(): ?string
    {
        return null;
    }

    /**
     * Unsupported method.
     */
    public function getDataLocked(): bool
    {
        return false;
    }

    /**
     * Unsupported method.
     */
    public function getFormFactory(): never
    {
        throw new BadMethodCallException('Buttons do not support adding children.');
    }

    /**
     * Unsupported method.
     *
     * @throws BadMethodCallException
     */
    public function getAction(): never
    {
        throw new BadMethodCallException('Buttons do not support actions.');
    }

    /**
     * Unsupported method.
     *
     * @throws BadMethodCallException
     */
    public function getMethod(): never
    {
        throw new BadMethodCallException('Buttons do not support methods.');
    }

    /**
     * Unsupported method.
     *
     * @throws BadMethodCallException
     */
    public function getRequestHandler(): never
    {
        throw new BadMethodCallException('Buttons do not support request handlers.');
    }

    /**
     * Unsupported method.
     */
    public function getAutoInitialize(): bool
    {
        return false;
    }

    /**
     * Unsupported method.
     */
    public function getInheritData(): bool
    {
        return false;
    }

    /**
     * Returns all options passed during the construction of the button.
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Returns whether a specific option exists.
     */
    public function hasOption(string $name): bool
    {
        return \array_key_exists($name, $this->options);
    }

    /**
     * Returns the value of a specific option.
     */
    public function getOption(string $name, mixed $default = null): mixed
    {
        return \array_key_exists($name, $this->options) ? $this->options[$name] : $default;
    }

    /**
     * Unsupported method.
     *
     * @throws BadMethodCallException
     */
    public function getIsEmptyCallback(): never
    {
        throw new BadMethodCallException('Buttons do not support "is empty" callback.');
    }

    /**
     * Unsupported method.
     */
    public function count(): int
    {
        return 0;
    }

    /**
     * Unsupported method.
     */
    public function getIterator(): \EmptyIterator
    {
        return new \EmptyIterator();
    }
}
