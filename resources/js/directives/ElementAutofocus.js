
var ElementAutofocus = {
    name : 'element-autofocus',
    bind: function(el) {
      var app = this
      let input = el.getElementsByTagName('INPUT')[0]
        Vue.nextTick(() => {
          input.focus();
        })
      }
};

export default ElementAutofocus