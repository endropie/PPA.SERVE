
let InsetValue = {
    name : 'inset-value',
    bind: function (el, binding, vnode) {
        // console.log('- Directive Input -')
        // console.log(vnode.data.attrs.name)
        // console.log(el)

        let name =  vnode.data.attrs.name === undefined
            ? vnode.componentOptions.propsData.name
            : vnode.data.attrs.name
            
        let value = binding.value
        
        var newInput = ('<input type="hidden" name="'+ name +'" value="'+ value +'">');
        $(el).append(newInput)
    
    },
    update: function (el, binding, vnode, oldvnode) {
        let name =  vnode.data.attrs.name === undefined
            ? vnode.componentOptions.propsData.name
            : vnode.data.attrs.name

        let value = binding.value
        $('input[type="hidden"][name="'+ name  +'"]').val(value)
    },

} 

export default InsetValue
