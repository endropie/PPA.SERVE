import Inputmask from 'inputmask'

var inputmask = {
    name : 'input-mask',
    bind: function (el, binding, vnode) {
        console.log(binding)
        Inputmask(binding.value).mask(el);        
    },
};

export default inputmask

/** Use in HTML
 * 
 * <input v-mask="{alias: 'datetime', inputFormat: 'dd/mm/yyyy'}" class="form-control" :value="formatDate(rsForm.date)" />
 * 
 * 
 * try:
 * 
 *  {alias: 'datetime', inputFormat: 'dd/mm/yyyy',  yearrange: { minyear: 1917, maxyear: 2999}, placeholder: 'dd/mm/yyyy' } 
**/