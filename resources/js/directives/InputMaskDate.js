import Inputmask from 'inputmask'
import moment from 'moment'


var datemask = {
    name : 'input-maskdate',
    bind: function (el, binding, vnode) {
        
        let input = $(el)
        
        Inputmask({
            alias: 'datetime',
            inputFormat:'dd/mm/yyyy',
            "onincomplete": function(){ 
                let v = moment($(input).val(), 'DD/MM/YYYY').format('DD/MM/YYYY')
                Inputmask.setValue(el, v);
            } 

        }).mask(el);        
    },
};

export default datemask