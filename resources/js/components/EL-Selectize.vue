<template>
  <select>
    <slot/>
  </select>
</template>

<script>
import $ from 'jquery'
import equal from 'deep-equal'
if (!$().selectize) {
  require('selectize')
}
function clean (options) {
  return options
    .map(option => ({
      text: option.text,
      value: option.value
    }))
}
export default {
  props: {
    value: {
      default: ''
    },
    settings: {
      type: Object,
      default: () => ({})
    },
    disabled: {
      type: Boolean,
      default: false
    }
  },
  data () {
    return {
      options: [],
      createdOptions: []
    }
  },
  mounted () {
    
    var self = this
    if (this.settings.create) {
      const create = this.settings.create
      this.settings.create = function(input,callback) {
        let option = null
        if (create === true) {
          if(self.$listeners.creating){
            option = false
            self.$emit('creating', input)
          }else{
            option = {
              text: input,
              value: input
            }
          }
        }
        else {
          option = create(input, callback)
        }
        self.createdOptions.push(option)
        return option
      }
      this.settings.render = {
          option_create: function (data, escape) {
              return '<div class="create">'
                    +'<strong>Add: ' + escape(data.input) + '</strong>'
                    +'</div>';
          }
      }
    }
    $(this.$el).selectize({
      selectOnTab: true,
      closeAfterSelect: true,
      onInitialize: () => {
        this.setValue()
      },
      onChange: value => {
        self.$emit('input', value)
      },
      ...this.settings
    })
    this.makeOptions(true)
    this.toggleDisabled(this.disabled)
  },
  destroyed () {
    if (this.$el.selectize) {
      this.$el.selectize.destroy()
    }
  },
  watch: {
    value (value, old) {
      if (!equal(value, old)) {
        this.setValue()
      }
    },
    disabled (value) {
      this.toggleDisabled(value)
    }
  },
  methods: {
    toggleDisabled (value) {
      if (value) {
        this.$el.selectize.disable()
      }
      else {
        this.$el.selectize.enable()
      }
    },
    makeOptions (justLocal = false) {
      const old = this.options
      let _new = []
      const nodes = this.$slots.default
      if (this.settings.options === undefined && nodes) {
        _new = nodes
          .filter(node => node.tag && node.tag.toLowerCase() === 'option')
          .map(node => {
            return {
              text: node.children ? node.children[0].text.trim() : null,
              value: node.data.domProps ? node.data.domProps.value : node.data.attrs.value
            }
          })
          .concat(this.createdOptions)
      }
      if (!equal(clean(old), clean(_new))) {
        this.options = _new
        if (!justLocal) {
          this.$el.selectize.clearOptions();
          const optionValues = this.options.map(o => o.value)
          Object.keys(this.$el.selectize.options)
            //IE11 fix, Object.values is not supported
            .map(key => this.$el.selectize.options[key])
            .filter(option => optionValues.every(v => !equal(v, option.value)))
            .forEach(option => this.$el.selectize.removeOption(option.value))
          this.$el.selectize.addOption(this.options)
          this.$el.selectize.refreshOptions(false)
          this.setValue()
        }
      }
    },
    setValue () {      
      if( this.$el.selectize.getValue() !== this.value)
      {
        this.$el.selectize.setValue(this.value, true)
      }
    },
  },
  beforeUpdate () {
    this.makeOptions()
  }
}
</script>

<style>
.el-form-item .selectize-control.single .selectize-input, 
.el-form-item .selectize-dropdown.single{
  border-color: #dcdfe6;
}
.el-form-item .selectize-control.single .selectize-input{
  background-image: linear-gradient(to bottom, #fefefe, #ffffff);
  padding: 0 8px;
}
.el-form-item .selectize-control.multi .selectize-input.has-items {
    padding: 3px 6px 2px
}
.el-form-item .selectize-control.multi .selectize-input > div {
    margin: 0 3px 1px 0;
    padding: 1px 4px;
}
.el-form-item .selectize-control.single,
.el-form-item .selectize-control.single .selectize-input{
    height: 40px;
    line-height: 40px;
}
.el-form-item--small .selectize-control.single,
.el-form-item--small .selectize-control.single .selectize-input{
    height: 32px;
    line-height: 32px;
}
.el-form-item--mini .selectize-control.single,
.el-form-item--mini .selectize-control.single .selectize-input{
    height: 28px;
    line-height: 28px;
}
.el-form-item.is-success .selectize-input{
    border-color: #67c23a;
    border-color: #dcdfe6;
}
.el-form-item.is-error .selectize-input{
    border-color: #f56c6c;
}
.el-form-item .selectize-input:focus {
    border-color: #409EFF;
    outline: 0;
}  
.selectize-control.plugin-remove_button .remove-single{
  color: #c2c7d0;
}

</style>