<template>
  <div>
    <div id="venue-search" class="autosuggest-container">
      <vue-autosuggest
          :suggestions="suggestions"
          :getSuggestionValue="getSuggestionValue"
          :input-props="inputProps"
          :limit="limit"
          @selected="onSelected">
        <template slot-scope="{suggestion}">
          {{suggestion.item.name}}
          <span class="light">– {{suggestion.item.city.name}}, {{suggestion.item.state ? suggestion.item.state.stateCode : ''}}</span>
        </template>
      </vue-autosuggest>
    </div>
    <div class="fields-container">
      <h2>TicketMaster Info</h2>
        <div v-if="venueFields">
          <Input
            label="Venue Title"
            :name="'fields[' + options.handle + '][title]'"
            handle="title"
            :value="venueFields.title || venueFields.name"
          />
          <Input
            label="Venue ID"
            :name="'fields[' + options.handle + '][tmVenueId]'"
            handle="tmVenueId"
            :value="venueFields.tmVenueId || venueFields.id"
          />
          <div class="md-layout md-gutter">
            <div class="md-layout-item" v-if="payloadFields.url">
              <Input
                label="URL"
                :name="getPayloadFieldName('[url]')"
                handle="url"
                :value="payloadFields.url"
              />
            </div>
            <div class="md-layout-item" v-if="payloadFields.address && payloadFields.address.line1">
              <Input
                label="Address"
                :name="getPayloadFieldName('[address][line1]')"
                handle="address"
                :value="payloadFields.address.line1"
              />
            </div>
            <div class="md-layout-item" v-if="payloadFields.address && payloadFields.address.line2">
              <Input
                label="Address"
                :name="getPayloadFieldName('[address][line2]')"
                handle="address"
                :value="payloadFields.address.line2"
              />
            </div>
            <div class="flex">
              <div class="md-layout-item" v-if="payloadFields.city">
                <Input
                  label="City"
                  :name="getPayloadFieldName('[city][name]')"
                  handle="city"
                  :value="payloadFields.city.name"
                />
              </div>
              <div class="md-layout-item" v-if="payloadFields.state">
                <Input
                  label="State"
                  :name="getPayloadFieldName('[state][stateCode]')"
                  handle="state"
                  :value="payloadFields.state.stateCode"
                />
              </div>
              <div class="md-layout-item" v-if="payloadFields.postalCode">
                <Input
                  label="Postal Code"
                  :name="getPayloadFieldName('[postalCode]')"
                  handle="state"
                  :value="payloadFields.postalCode"
                />
              </div>
            </div>
          <div class="md-layout-item" v-if="payloadFields.boxOfficeInfo">
            <Redactor
              label="Phone Number"
              handle="phoneNumberDetail"
              :name="getPayloadFieldName('[boxOfficeInfo][phoneNumberDetail]')"
              v-model="payloadFields.boxOfficeInfo.phoneNumberDetail"
            />
          </div>

          <div class="md-layout-item" v-if="payloadFields.boxOfficeInfo">
            <Redactor
              label="Open Hours"
              handle="openHoursDetail"
              :name="getPayloadFieldName('[boxOfficeInfo][openHoursDetail]')"
              v-model="payloadFields.boxOfficeInfo.openHoursDetail"
            />
          </div>

          <div class="md-layout-item" v-if="payloadFields.boxOfficeInfo">
            <Redactor
              label="Accepted Payments"
              handle="acceptedPaymentDetail"
              :name="getPayloadFieldName('[boxOfficeInfo][acceptedPaymentDetail]')"
              :value="payloadFields.boxOfficeInfo.acceptedPaymentDetail"
            />
          </div>

          <div class="md-layout-item" v-if="payloadFields.boxOfficeInfo">
            <Redactor
              label="Will Call"
              :name="getPayloadFieldName('[boxOfficeInfo][willCallDetail]')"
              handle="willCallDetail"
              :value="payloadFields.boxOfficeInfo.willCallDetail"
            />
          </div>

          <div class="md-layout-item" v-if="payloadFields.parkingDetail">
            <Redactor
              label="Parking"
              :name="getPayloadFieldName('[parkingDetail]')"
              handle="parkingDetail"
              :value="payloadFields.parkingDetail"
            />
          </div>

          <div class="md-layout-item" v-if="payloadFields.accessibleSeatingDetail">
            <Redactor
              label="Accessible Seating"
              :name="getPayloadFieldName('[accessibleSeatingDetail]')"
              handle="accessibleSeatingDetail"
              :value="payloadFields.accessibleSeatingDetail"
            />
          </div>

          <div class="md-layout-item" v-if="payloadFields.generalInfo">
            <Redactor
              label="General Info"
              :name="getPayloadFieldName('[generalInfo][generalRule]')"
              handle="generalRule"
              :value="payloadFields.generalInfo.generalRule"
            />
          </div>

          <div class="md-layout-item" v-if="payloadFields.generalInfo">
            <Redactor
              label="Children Info"
              :name="getPayloadFieldName('[generalInfo][childRule]')"
              handle="childRule"
              :value="payloadFields.generalInfo.childRule"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="js">
import { Component, Vue } from 'vue-property-decorator';
import { VueAutosuggest } from 'vue-autosuggest';
import qs from 'qs';
import { t } from '../filters/translate';
import Input from './Input';
import Textarea from './Textarea';
import Redactor from './Redactor';
import get from 'lodash.get';

@Component({
  components: {
    VueAutosuggest,
    Input,
    Textarea,
    Redactor
  },
  props: {
    options: Object,
    venue: Object
  }
})
export default class VenueSearch extends Vue {
  // Properties
  // =====================================================================
  suggestions = [{ data: [] }];
  limit = 5;
  initialValue = '';

  // Getters
  // =====================================================================
  get venueFields () {
    if (typeof this.venue === 'string') {
      return JSON.parse(this.venue);
    }

    return this.venue;
  }

  get payloadFields() {
    return get(this.venueFields, 'payload') || this.venueFields;
  }

  get inputProps () {
    return {
      onInputChange: this.onInputChange,
      class: 'text nicetext fullwidth',
      placeholder: t('Search for a venue'),
      initialValue: this.initialValue,
    };
  }

  created() {
    console.log('created event search');
    // console.log(this.venue);
  }

  mounted() {
    console.log('venue search', this);
  }

  onSelected(option) {
    this.venue = option.item;
    console.log(this.venueFields);
    this.$emit('selected', option.item);
  }

  onInputChange(text) {
    if (text === '' || text === undefined || text.length < 3) {
      return;
    }

    clearTimeout(this.timeout);
    this.timeout = setTimeout(() => {
      this.search(text);
    }, 450);
  }

  search(value) {
    const params = qs.stringify({
      apikey: this.$props.options.apiKey,
      keyword: value
    });
    fetch(`https://app.ticketmaster.com/discovery/v2/venues.json?${params}`)
      .then(res => res.json())
      .then((res) => {
        if (res && res._embedded && res._embedded.venues.length) {
          this.suggestions = [{ data: res._embedded.venues }];
        }
      })
      .catch();
  }

  getPayloadFieldName(handle) {
    return `fields[${this.options.handle}][payload]${handle}`;
  }

  getSuggestionValue(suggestion) {
    return suggestion.item.name || suggestion.item;
  }
}
</script>

<style lang="scss" scoped>
.fields-container {
  background: #eee;
  padding: 10px;
  border: 1px solid darken(#eee, 10%);
}
</style>
