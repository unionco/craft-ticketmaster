<template>
  <div v-if="venue">
    <Input
      label="Venue Title"
      name="title"
      handle="title"
      :value="venue.title || venue.name"
    />
    <Input
      label="Venue ID"
      name="fields[tmVenueId]"
      handle="tmVenueId"
      :value="venue.tmVenueId || venue.id"
    />

    <div class="md-layout md-gutter" v-if="venue.payload">
      <div class="md-layout-item">
        <Input
          label="URL"
          name="fields[payload][url]"
          handle="url"
          :value="venue.payload.url"
        />
      </div>
    </div>

    <div class="md-layout md-gutter" v-if="venue.payload">
      <div class="md-layout-item" v-if="venue.payload.address && venue.payload.address.line1">
        <Input
          label="Address"
          name="fields[payload][address][line1]"
          handle="address"
          :value="venue.payload.address.line1"
        />
      </div>
      <div class="md-layout-item" v-if="venue.payload.address && venue.payload.address.line2">
        <Input
          label="Address"
          name="fields[payload][address][line2]"
          handle="address"
          :value="venue.payload.address.line2"
        />
      </div>
    </div>

    <div class="md-layout md-gutter" v-if="venue.payload">
      <div class="md-layout-item" v-if="venue.payload.city">
        <Input
          label="City"
          name="fields[payload][city][name]"
          handle="city"
          :value="venue.payload.city.name"
        />
      </div>
      <div class="md-layout-item" v-if="venue.payload.state">
        <Input
          label="City"
          name="fields[payload][state][stateCode]"
          handle="state"
          :value="venue.payload.state.stateCode"
        />
      </div>
      <div class="md-layout-item">
        <Input
          label="City"
          name="fields[payload][postalCode]"
          handle="state"
          :value="venue.payload.postalCode"
        />
      </div>
    </div>
    
    <div class="md-layout md-gutter" v-if="venue.payload">
      <div class="md-layout-item" v-if="venue.payload.boxOfficeInfo">
        <Redactor
          label="Phone Number"
          handle="phoneNumberDetail"
          name="fields[payload][boxOfficeInfo][phoneNumberDetail]"
          v-model="venue.payload.boxOfficeInfo.phoneNumberDetail"
        />
      </div>
    </div>

    <div class="md-layout md-gutter" v-if="venue.payload">
      <div class="md-layout-item" v-if="venue.payload.boxOfficeInfo">
        <Redactor
          label="Open Hours"
          handle="openHoursDetail"
          name="fields[payload][boxOfficeInfo][openHoursDetail]"
          v-model="venue.payload.boxOfficeInfo.openHoursDetail"
        />
      </div>
    </div>
    
    <div class="md-layout md-gutter" v-if="venue.payload">
      <div class="md-layout-item" v-if="venue.payload.boxOfficeInfo">
        <Redactor
          label="Accepted Payments"
          handle="acceptedPaymentDetail"
          name="fields[payload][boxOfficeInfo][acceptedPaymentDetail]"
          :value="venue.payload.boxOfficeInfo.acceptedPaymentDetail"
        />
      </div>
    </div>
    
    <div class="md-layout md-gutter" v-if="venue.payload">
      <div class="md-layout-item" v-if="venue.payload.boxOfficeInfo">
        <Redactor
          label="Will Call"
          name="fields[payload][boxOfficeInfo][willCallDetail]"
          handle="willCallDetail"
          :value="venue.payload.boxOfficeInfo.willCallDetail"
        />
      </div>
    </div>
    
    <div class="md-layout md-gutter" v-if="venue.payload">
      <div class="md-layout-item" v-if="venue.payload.parkingDetail">
        <Redactor
          label="Parking"
          name="fields[payload][parkingDetail]"
          handle="parkingDetail"
          :value="venue.payload.parkingDetail"
        />
      </div>
    </div>
    
    <div class="md-layout md-gutter" v-if="venue.payload">
      <div class="md-layout-item" v-if="venue.payload.accessibleSeatingDetail">
        <Redactor
          label="Accessible Seating"
          name="fields[payload][accessibleSeatingDetail]"
          handle="accessibleSeatingDetail"
          :value="venue.payload.accessibleSeatingDetail"
        />
      </div>
    </div>
    
    <div class="md-layout md-gutter" v-if="venue.payload && venue.payload.generalInfo">
      <div class="md-layout-item">
        <Redactor
          label="General Info"
          name="fields[payload][generalInfo][generalRule]"
          handle="generalRule"
          :value="venue.payload.generalInfo.generalRule"
        />
      </div>
    </div>
    
    <div class="md-layout md-gutter" v-if="venue.payload && venue.payload.generalInfo">
      <div class="md-layout-item">
        <Redactor
          label="Children Info"
          name="fields[payload][generalInfo][childRule]"
          handle="childRule"
          :value="venue.payload.generalInfo.childRule"
        />
      </div>
    </div>
  </div>
</template>

<script lang="js">
import { Component, Vue } from 'vue-property-decorator';
import Input from './Input';
import Textarea from './Textarea';
import Redactor from './Redactor';

@Component({
  props: {
    venue: Object
  },
  components: {
    Input,
    Textarea,
    Redactor
  }
})
export default class App extends Vue {
  //
}
</script>
<style lang="scss" scoped>
.md-layout-item {
  margin-bottom: 24px !important;
}
</style>
