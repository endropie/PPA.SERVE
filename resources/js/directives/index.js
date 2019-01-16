import Vue from 'vue';

import InsetValue from './InsetValue'
import InputMask from './InputMask'
import InputMaskDate from './InputMaskDate'
import ElementMaskDate from './ElementMaskDate'



Vue.directive(InsetValue.name, InsetValue)
Vue.directive(InputMask.name, InputMask)
Vue.directive(InputMaskDate.name, InputMaskDate)
Vue.directive(ElementMaskDate.name, ElementMaskDate)


export default {}