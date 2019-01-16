// import {$,jQuery} from 'jquery';
import Inputmask from 'inputmask'
import moment from 'moment'

function set(ob, path, value) {
    var schema = ob;  // a moving reference to internal objects within obj
    var pList = path.split('.');
    var len = pList.length;
    for(var i = 0; i < len-1; i++) {
        var elem = pList[i];
        if( !schema[elem] ) schema[elem] = {}
        schema = schema[elem];
    }

    schema[pList[len-1]] = value;
}

var elementDateMask = {
    name : 'element-maskdate',
    twoWay: true,
    bind: function (el, binding, vnode) {
        var format = vnode.componentInstance.format ? vnode.componentInstance.format  : 'yyyy-MM-dd'
        var valueFormat = vnode.componentInstance.valueFormat ? vnode.componentInstance.valueFormat.toUpperCase()  : null

        // console.log(vnode.componentInstance.format)
        // console.log(vnode.componentInstance.valueFormat)
        let input = el.getElementsByTagName('INPUT')[0]
        Inputmask('datetime', {inputFormat: format.toLowerCase() }).mask(input)

        let handler = function(e) {
            if($(input).val())
            {
                var dateValue = moment($(input).val(), format.toUpperCase()).format(valueFormat)
                set(vnode.context, vnode.data.model.expression, dateValue)
            }
            
        }
        input.addEventListener('change', handler);

        
        
        
    },
};


export default elementDateMask