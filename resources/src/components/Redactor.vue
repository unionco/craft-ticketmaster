<template>
  <div class="field" :id="handle + '-field'">
  <div class="heading">
    <label :id="handle + '-label'" :for="handle">{{ label }}</label>
  </div>
  <div class="input ltr">
    <textarea
      ref="redactor"
      :name="name"
      :placeholder="placeholder"
      :value="value" 
    />
  </div>
  </div>
</template>

<script lang="js">
import { Component, Watch, Vue } from 'vue-property-decorator';
import { decodeHtml as dh } from '../helpers/decode';

const $R = window.$R;

@Component({
  props: {
    label: String,
    name: String,
    handle: String,
    value: {
      default: '',
      type: String
    },
    placeholder: {
      type: String,
      default: null
    },
    config: {
      default: {
        allowedTags: ['p']
      },
      type: Object
    }
  }
})
export default class Redactor extends Vue {
  @Watch('value')
  onValueChange(newValue) {
    if (!this.redactor.editor.isFocus()) {
      this.redactor.source.setCode(newValue);
    }
  }

  //
  redactor = false;
  
  mounted() {
    this.init();
  }
  
  beforeDestroy() {
    this.destroy();
  }

  init() {
    const callbacks = {
      changed: (html) => {
        this.handleInput(html);
        return html;
      }
    };

    // clean up
    this.$props.value = dh(this.$props.value);

    // extend config
    Vue.set(this.config, 'callbacks', callbacks);

    // call Redactor
    const app = $R(this.$refs.redactor, this.config);

    // set instance
    this.redactor = app;
    this.$parent.redactor = app;

    // set code
    this.redactor.source.setCode(dh(this.$props.value));
  }

  destroy () {
  // Call destroy on redactor to cleanup event handlers
    $R(this.$refs.redactor, 'destroy');
    
    // unset instance for garbage collection
    this.redactor = null;
    this.$parent.redactor = null;
  }

  handleInput (val) {
    this.$emit('input', val);
  }
}
</script>

<style lang="scss" scoped>
.field {
  width: 100%;
}
</style>
