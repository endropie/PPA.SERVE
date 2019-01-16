import Inputmask from 'inputmask'

var ElementMask = {
    name : 'element-mask',
    bind: function (el, binding, vnode) {
        Inputmask(binding.value).mask(el.getElementsByTagName('INPUT')[0]);        
    },
};

export default ElementMask

/** Use in HTML
 * 
 * <input v-mask="{alias: 'datetime', inputFormat: 'dd/mm/yyyy'}" class="form-control" :value="formatDate(rsForm.date)" />
 * 
 * 
 * try:
 * 
 *  {alias: 'datetime', inputFormat: 'dd/mm/yyyy',  yearrange: { minyear: 1917, maxyear: 2999}, placeholder: 'dd/mm/yyyy' } 
**/