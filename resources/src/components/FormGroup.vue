<template>
  <tr v-if="group" class="form-group">
    <td class="form-group-label">
      {{ label }}
    </td>
    <td class="form-group-fields" :class="{ 'has-children' : hasChildren() }">
      <table class="hasChildren" v-if="hasChildren()">
        <form-group
          v-for="(children, index) in group"
          v-bind:key="index"
          :label="index"
          :group="children"
          :name="`${name}[${label}]`"
          ></form-group>
      </table>
      <textarea ref="textarea" v-else class="text fullwidth" :name="`${name}[${label}]`" :value="group" />
    </td>
  </tr>
</template>
<script lang="js">
import { Component, Vue } from 'vue-property-decorator';
import autosize from 'autosize';
import get from 'lodash.get';
import Input from './Input';

@Component({
  props: {
    label: String,
    group: Object,
    mapped: Object,
    name: String,
    mapName: String,
    mapNameDot: String,
    fields: Array
  },
  components: {
    Input
  }
})
export default class FormGroup extends Vue {
  //
  label = '';
  name = '';
  mapName = '';
  mapNameDot = '';
  group = {};
  fields = [];
  mapped = {};

  get mapLabel() {
    if (!this.mapped || !this.label) {
      return '';
    }

    let path = '';
    if (this.mapNameDot) {
      path = `${this.mapNameDot}.${this.label}`;
    } else {
      path = this.label;
    }

    const payload = JSON.parse(JSON.stringify(this.mapped));

    return get(payload, path) || 'skip';
  }

  mounted() {
    autosize(this.$refs.textarea);
  }

  hasChildren() {
    return typeof this.group === 'object' && this.group !== null;
  }
}
</script>
<style lang="scss" scoped>
.form-group {
  vertical-align: middle;

  &-select {
    border: 1px solid rgba(0, 0, 20, 0.1);
    padding: 5px;
    text-align: center;
  }

  &-label {
    background: #eee;
    border: 1px solid rgba(0, 0, 20, 0.1);
    padding: 5px;
    text-transform: capitalize;
  }

  table {
    width: 100%;
  }
  input {
    max-width: 100%;
  }
}
</style>
