
import PhoneNumberFieldtype from './components/fieldtypes/PhoneNumberFieldtype.vue';

Statamic.booting(() => {
    Statamic.$components.register('phone_number-fieldtype', PhoneNumberFieldtype);
});
