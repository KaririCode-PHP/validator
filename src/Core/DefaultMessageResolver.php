<?php

declare(strict_types=1);

namespace KaririCode\Validator\Core;

use KaririCode\Validator\Contract\MessageResolver;

/**
 * Default i18n message resolver with locale fallback chain.
 *
 * Resolves validation error messages by error code, supporting
 * multiple locales (PT-BR, EN, ES) with fallback to English.
 *
 * @package KaririCode\Validator\Core
 * @author  Walmir Silva <walmir.silva@kariricode.org>
 * @since   3.1.0 ARFA 1.3
 */
final class DefaultMessageResolver implements MessageResolver
{
    /** @var array<string, array<string, string>> locale => [errorCode => message] */
    private array $messages;

    /**
     * @param string                                     $defaultLocale Default locale
     * @param array<string, array<string, string>>|null  $messages      Custom messages (null = built-in)
     */
    public function __construct(
        private readonly string $defaultLocale = 'en',
        ?array $messages = null,
    ) {
        $this->messages = $messages ?? $this->builtInMessages();
    }

    /**
     * @param string               $errorCode  The error code to resolve
     * @param array<string, mixed> $parameters Token replacements for message interpolation
     * @param string|null          $locale     Locale override (falls back to default)
     */
    #[\Override]
    public function resolve(
        string $errorCode,
        array $parameters = [],
        ?string $locale = null,
    ): string {
        $locale ??= $this->defaultLocale;
        $message = $this->messages[$locale][$errorCode]
            ?? $this->messages['en'][$errorCode]
            ?? "Validation failed for {$errorCode}.";

        return $this->interpolate($message, $parameters);
    }

    /**
     * Registers a custom message for a given locale and error code.
     */
    public function addMessage(string $locale, string $errorCode, string $message): void
    {
        $this->messages[$locale][$errorCode] = $message;
    }

    /**
     * Registers a batch of messages for a locale.
     *
     * @param array<string, string> $messages errorCode => message
     */
    public function addMessages(string $locale, array $messages): void
    {
        foreach ($messages as $errorCode => $message) {
            $this->messages[$locale][$errorCode] = $message;
        }
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function interpolate(string $message, array $parameters): string
    {
        $replacements = [];
        foreach ($parameters as $key => $value) {
            if (\is_scalar($value) || $value === null) {
                $replacements['{' . $key . '}'] = (string) ($value ?? '');
            }
        }

        return strtr($message, $replacements);
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function builtInMessages(): array
    {
        return [
            'en' => [
                // Logic
                'logic.required' => 'The {field} field is required.',
                'logic.choice' => 'The {field} value is not a valid choice.',
                'logic.nullable' => 'The {field} must be null or valid.',
                'logic.not_null' => 'The {field} must not be null.',
                'logic.callback' => 'The {field} is invalid.',
                'logic.at_least_one_of' => 'The {field} must pass at least one validation rule.',
                // String
                'string.email' => 'The {field} must be a valid email address.',
                'string.url' => 'The {field} must be a valid URL.',
                'string.length' => 'The {field} must be between {min} and {max} characters.',
                'string.pattern' => 'The {field} format is invalid.',
                'string.not_empty' => 'The {field} must not be empty.',
                'string.alpha' => 'The {field} must contain only letters.',
                'string.alphanumeric' => 'The {field} must contain only letters and numbers.',
                'string.uuid' => 'The {field} must be a valid UUID.',
                'string.ulid' => 'The {field} must be a valid ULID.',
                'string.ip' => 'The {field} must be a valid IP address.',
                'string.json' => 'The {field} must be valid JSON.',
                'string.hostname' => 'The {field} must be a valid hostname.',
                'string.slug' => 'The {field} must be a valid URL slug.',
                'string.starts_with' => 'The {field} must start with {prefix}.',
                'string.ends_with' => 'The {field} must end with {suffix}.',
                // Numeric
                'numeric.integer' => 'The {field} must be an integer.',
                'numeric.positive' => 'The {field} must be a positive number.',
                'numeric.range' => 'The {field} must be between {min} and {max}.',
                'numeric.negative' => 'The {field} must be a negative number.',
                'numeric.negative_or_zero' => 'The {field} must be negative or zero.',
                'numeric.decimal' => 'The {field} must be a decimal number with at most {places} decimal places.',
                // Comparison
                'comparison.equal_to' => 'The {field} must be equal to {expected}.',
                'comparison.not_equal_to' => 'The {field} must not be equal to {expected}.',
                'comparison.greater_than' => 'The {field} must be greater than {threshold}.',
                'comparison.greater_than_or_equal' => 'The {field} must be greater than or equal to {threshold}.',
                'comparison.less_than' => 'The {field} must be less than {threshold}.',
                'comparison.less_than_or_equal' => 'The {field} must be less than or equal to {threshold}.',
                'comparison.between' => 'The {field} must be between {min} and {max}.',
                // Date
                'date.format' => 'The {field} must match the date format {format}.',
                'date.before' => 'The {field} must be a date before {before}.',
                'date.after' => 'The {field} must be a date after {after}.',
                'date.between' => 'The {field} must be a date between {after} and {before}.',
                // Type
                'type.mismatch' => 'The {field} must be of type {type}.',
                'type.instance_of' => 'The {field} must be an instance of {class}.',
                // Collection
                'collection.count' => 'The {field} must contain between {min} and {max} items.',
                'collection.unique' => 'The {field} must contain only unique values.',
                'collection.each' => 'Each item in {field} must be valid.',
                'collection.key_exists' => 'The {field} must contain the key {key}.',
                // Cross-field
                'crossfield.same_as' => 'The {field} must match {other}.',
                'crossfield.different_from' => 'The {field} must differ from {other}.',
                'crossfield.required_with' => 'The {field} is required when {other} is present.',
                'crossfield.required_without' => 'The {field} is required when {other} is absent.',
                // Financial
                'financial.luhn' => 'The {field} is not a valid card number.',
                'financial.iban' => 'The {field} is not a valid IBAN.',
                'financial.bic' => 'The {field} is not a valid BIC/SWIFT code.',
                // Brazilian
                'brazilian.cpf' => 'The {field} is not a valid CPF.',
                'brazilian.cnpj' => 'The {field} is not a valid CNPJ.',
                'brazilian.cep' => 'The {field} is not a valid CEP.',
                'brazilian.pis' => 'The {field} is not a valid PIS/PASEP.',
            ],
            'pt_BR' => [
                'logic.required' => 'O campo {field} é obrigatório.',
                'logic.choice' => 'O valor de {field} não é uma opção válida.',
                'logic.nullable' => 'O campo {field} deve ser nulo ou válido.',
                'logic.not_null' => 'O campo {field} não pode ser nulo.',
                'logic.callback' => 'O campo {field} é inválido.',
                'logic.at_least_one_of' => 'O campo {field} deve passar em pelo menos uma regra.',
                'string.email' => 'O campo {field} deve ser um endereço de e-mail válido.',
                'string.url' => 'O campo {field} deve ser uma URL válida.',
                'string.length' => 'O campo {field} deve ter entre {min} e {max} caracteres.',
                'string.pattern' => 'O formato de {field} é inválido.',
                'string.not_empty' => 'O campo {field} não pode estar vazio.',
                'string.alpha' => 'O campo {field} deve conter apenas letras.',
                'string.alphanumeric' => 'O campo {field} deve conter apenas letras e números.',
                'string.uuid' => 'O campo {field} deve ser um UUID válido.',
                'string.ulid' => 'O campo {field} deve ser um ULID válido.',
                'string.ip' => 'O campo {field} deve ser um endereço IP válido.',
                'string.json' => 'O campo {field} deve ser um JSON válido.',
                'string.hostname' => 'O campo {field} deve ser um hostname válido.',
                'string.slug' => 'O campo {field} deve ser um slug válido.',
                'string.starts_with' => 'O campo {field} deve começar com {prefix}.',
                'string.ends_with' => 'O campo {field} deve terminar com {suffix}.',
                'numeric.integer' => 'O campo {field} deve ser um número inteiro.',
                'numeric.positive' => 'O campo {field} deve ser um número positivo.',
                'numeric.range' => 'O campo {field} deve estar entre {min} e {max}.',
                'numeric.negative' => 'O campo {field} deve ser um número negativo.',
                'numeric.negative_or_zero' => 'O campo {field} deve ser negativo ou zero.',
                'numeric.decimal' => 'O campo {field} deve ser decimal com no máximo {places} casas decimais.',
                'comparison.equal_to' => 'O campo {field} deve ser igual a {expected}.',
                'comparison.not_equal_to' => 'O campo {field} não deve ser igual a {expected}.',
                'comparison.greater_than' => 'O campo {field} deve ser maior que {threshold}.',
                'comparison.greater_than_or_equal' => 'O campo {field} deve ser maior ou igual a {threshold}.',
                'comparison.less_than' => 'O campo {field} deve ser menor que {threshold}.',
                'comparison.less_than_or_equal' => 'O campo {field} deve ser menor ou igual a {threshold}.',
                'comparison.between' => 'O campo {field} deve estar entre {min} e {max}.',
                'date.format' => 'O campo {field} deve corresponder ao formato {format}.',
                'date.before' => 'O campo {field} deve ser anterior a {before}.',
                'date.after' => 'O campo {field} deve ser posterior a {after}.',
                'date.between' => 'O campo {field} deve estar entre {after} e {before}.',
                'type.mismatch' => 'O campo {field} deve ser do tipo {type}.',
                'type.instance_of' => 'O campo {field} deve ser uma instância de {class}.',
                'collection.count' => 'O campo {field} deve conter entre {min} e {max} itens.',
                'collection.unique' => 'O campo {field} deve conter apenas valores únicos.',
                'collection.each' => 'Cada item em {field} deve ser válido.',
                'collection.key_exists' => 'O campo {field} deve conter a chave {key}.',
                'crossfield.same_as' => 'O campo {field} deve ser igual a {other}.',
                'crossfield.different_from' => 'O campo {field} deve ser diferente de {other}.',
                'crossfield.required_with' => 'O campo {field} é obrigatório quando {other} está presente.',
                'crossfield.required_without' => 'O campo {field} é obrigatório quando {other} está ausente.',
                'financial.luhn' => 'O campo {field} não é um número de cartão válido.',
                'financial.iban' => 'O campo {field} não é um IBAN válido.',
                'financial.bic' => 'O campo {field} não é um código BIC/SWIFT válido.',
                'brazilian.cpf' => 'O campo {field} não é um CPF válido.',
                'brazilian.cnpj' => 'O campo {field} não é um CNPJ válido.',
                'brazilian.cep' => 'O campo {field} não é um CEP válido.',
                'brazilian.pis' => 'O campo {field} não é um PIS/PASEP válido.',
            ],
            'es' => [
                'logic.required' => 'El campo {field} es obligatorio.',
                'string.email' => 'El campo {field} debe ser una dirección de correo electrónico válida.',
                'string.url' => 'El campo {field} debe ser una URL válida.',
                'string.length' => 'El campo {field} debe tener entre {min} y {max} caracteres.',
                'numeric.range' => 'El campo {field} debe estar entre {min} y {max}.',
                'brazilian.cpf' => 'El campo {field} no es un CPF válido.',
                'brazilian.cnpj' => 'El campo {field} no es un CNPJ válido.',
            ],
        ];
    }
}
