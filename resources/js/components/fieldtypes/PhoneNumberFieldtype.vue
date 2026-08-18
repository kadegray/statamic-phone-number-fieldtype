<script setup>
import { ref, onMounted } from 'vue';
import { Fieldtype } from '@statamic/cms';
import { Input } from '@statamic/cms/ui';
import 'intl-tel-input/build/css/intlTelInput.css';
import intlTelInput from 'intl-tel-input';

const utilsScriptUrl = '/vendor/statamic-phone-number-fieldtype/js/utils.js';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose, update, name, isReadOnly } = Fieldtype.use(emit, props);
defineExpose(expose);

const inputComponent = ref(null);
const iti = ref(null);
const errorMessage = ref(null);

const errors = {
    // The number has an invalid country calling code.
    INVALID_COUNTRY_CODE: 'Invalid country calling code',

    // This indicates the string passed is not a valid number. Either the string
    // had less than 3 digits in it or had an invalid phone-context parameter.
    // More specifically, the number failed to match the regular expression
    // VALID_PHONE_NUMBER, RFC3966_GLOBAL_NUMBER_DIGITS, or RFC3966_DOMAINNAME.
    NOT_A_NUMBER: 'This number did not seem to be a phone number',

    // This indicates the string started with an international dialing prefix, but
    // after this was stripped from the number, had less digits than any valid
    // phone number (including country calling code) could have.
    TOO_SHORT_AFTER_IDD: 'This phone number too short after international prefix (IDD).',

    // This indicates the string, after any country calling code has been
    // stripped, had less digits than any valid phone number could have.
    TOO_SHORT_NSN: 'This number is too short to be a phone number.',

    // This indicates the string had more digits than any valid phone number could
    // have.
    TOO_LONG: 'This number is too long to be a phone number.',

    // The number is longer than the shortest valid numbers for this region,
    // shorter than the longest valid numbers for this region, and does not itself
    // have a number length that matches valid numbers for this region.
    // This can also be returned in the case where
    // isPossibleNumberForTypeWithReason was called, and there are no numbers of
    // this type at all for this region.
    INVALID_LENGTH: 'The number is longer than all valid numbers for this region.',
};

onMounted(async () => {
    const currentLang = document.documentElement.lang;
    let localizedCountries = null;
    if (currentLang !== 'en') {
        let localizedCountriesResponse = await fetch(`/!/statamic-phone-number-fieldtype/${currentLang}/countries`);
        localizedCountries = await localizedCountriesResponse.json();
    }

    const root = inputComponent.value.$el;
    const input = root.matches('input') ? root : root.querySelector('input');

    iti.value = intlTelInput(input, {
        allowDropdown: props.config.show_country_select ?? true,
        initialCountry: props.config.initial_country ?? null,
        excludeCountries: props.config.exclude_countries ?? [],
        onlyCountries: props.config.only_countries ?? [],
        preferredCountries: props.config.preferred_countries ?? ['us', 'gb'],
        localizedCountries: localizedCountries,
        utilsScript: utilsScriptUrl,
    });
});

function inputEvent(rawValue) {

    const validationError = iti.value.getValidationError();

    switch (validationError) {

        case intlTelInputUtils.validationError.IS_POSSIBLE:
        case intlTelInputUtils.validationError.IS_POSSIBLE_LOCAL_ONLY:
            errorMessage.value = null;
            break;

        case intlTelInputUtils.validationError.INVALID_COUNTRY_CODE:
            errorMessage.value = errors.INVALID_COUNTRY_CODE;
            break;

        case intlTelInputUtils.validationError.INVALID_LENGTH:
            errorMessage.value = errors.INVALID_LENGTH;
            break;

        case intlTelInputUtils.validationError.TOO_LONG:
            errorMessage.value = errors.TOO_LONG;
            break;

        case intlTelInputUtils.validationError.TOO_SHORT_AFTER_IDD:
            errorMessage.value = errors.TOO_SHORT_AFTER_IDD;
            break;

        case intlTelInputUtils.validationError.TOO_SHORT_NSN:
            errorMessage.value = errors.TOO_SHORT_NSN;
            break;
    }

    if (null === errorMessage.value) {
        update(iti.value.getNumber(intlTelInputUtils.numberFormat.E164));

        return;
    }

    update(rawValue);
}
</script>

<template>
    <div>
        <Input
            ref="inputComponent"
            type="tel"
            :name="name"
            :model-value="value"
            :read-only="isReadOnly"
            :disabled="isReadOnly"
            :placeholder="config.placeholder"
            @update:model-value="inputEvent"
        />
        <div class="help-block text-red-500 mt-1 mb-0" v-if="errorMessage" v-text="errorMessage" />
    </div>
</template>

<style>

    .iti {
        width: 100%;
    }

    .iti__flag {
        background-image: url('/vendor/statamic-phone-number-fieldtype/images/vendor/intl-tel-input/build/flags.png');
    }

    @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
        .iti__flag {
            background-image: url('/vendor/statamic-phone-number-fieldtype/images/vendor/intl-tel-input/build/flags@2x.png');
        }
    }

    .iti__country-list {
        z-index: 10;
    }

    .dark .iti__country-list {
        background-color: var(--theme-color-content-bg);
        border-color: var(--theme-color-content-border);
        box-shadow: 1px 1px 4px rgba(0, 0, 0, 0.6);
    }

    .dark .iti__country.iti__highlight {
        background-color: rgba(255, 255, 255, 0.1);
    }

    .dark .iti__divider {
        border-bottom-color: var(--theme-color-content-border);
    }

    .dark .iti__arrow {
        border-top-color: #999;
    }

    .dark .iti__arrow--up {
        border-bottom-color: #999;
    }

</style>
