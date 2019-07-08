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

@Component({
  components: {
    VueAutosuggest,
    Input
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
    return JSON.parse(this.venue);
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
    console.log('created venue search');
    console.log(this.venue);
  }

  mounted() {
    console.log(this.venueFields)
  }

  onSelected(option) {
    this.venue = option.item
    this.$emit('selected', option.item);
  }

  onInputChange(text) {
    if (text === '' || text === undefined || text.length < 3) {
      return;
    }

    clearTimeout(this.timeout);
    this.timeout = setTimeout(() => {
      this.search(text);
    }, 750);
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
  display: flex;
  justify-content: space-around;
  align-items: center;
}
</style>
