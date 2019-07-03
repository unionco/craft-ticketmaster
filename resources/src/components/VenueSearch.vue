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
    <div class="u-grey">
      other fields go here
    </div>
  </div>
</template>

<script lang="js">
import { Component, Vue } from 'vue-property-decorator';
import { VueAutosuggest } from 'vue-autosuggest';
import qs from 'qs';
import { t } from '../filters/translate';

@Component({
  components: {
    VueAutosuggest,
  },
  props: {
    options: Object
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
  }

  onSelected(option) {
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
.u-grey {
  background: #eee;
  border: 1px solid darken(#eee, 10%);
}
</style>
