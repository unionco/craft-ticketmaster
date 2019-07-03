<template>
  <table class="eventform" v-if="payload.id">
    <FormGroup 
      v-for="(group, index) in payload"
      v-bind:key="index"
      :label="index"
      :group="payload[index]"
      :fields="fields"
      :mapped="mapped"
      name="fields[payload]"
      mapName="fields[mapped]"
      mapNameDot="" />
  </table>
</template>
<script lang="js">
import { Component, Vue } from 'vue-property-decorator';
import FormGroup from './components/FormGroup';

@Component({
  props: {
    payload: Object,
    mapped: Object,
    fields: Array
  },
  components: {
    FormGroup
  }
})
export default class EventForm extends Vue {
  //
  payload = {};
  mapped = {};
  fields = [];
}
</script>
<style lang="scss" scoped>
.eventform {
  max-width: 100%;
  width: 100%;
}
</style>
