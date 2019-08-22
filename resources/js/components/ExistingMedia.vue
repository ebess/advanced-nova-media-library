<template>
  <div class="fixed pin-l pin-t p-8 h-full w-full z-50" :class="{'hidden': !open, 'flex': open}">
    <div class="absolute bg-black opacity-75 pin-l pin-t h-full w-full" v-on:click="close"></div>
    <div class="flex flex-col bg-white p-4 h-full relative w-full">
      <div class="border-b border-40 pb-4 mb-4">
        <div class="flex -mx-4">
          <div class="px-4 self-center">
            <h3>Existing Media</h3>
          </div>
          <div class="px-4 self-center">
            <div class="relative">
              <icon type="search" class="absolute search-icon-center ml-3 text-70" />
              <input type="search"
                     placeholder="Search by name or file name"
                     class="pl-search form-control form-input form-input-bordered w-full"
                     v-model="requestParams.search_text"
                     @input.native="search"
                     v-on:keyup.enter="search">
            </div>
          </div>
          <div class="px-4 ml-auto self-center">
            <button type="button" class="form-file-btn btn btn-default btn-primary" v-on:click="close">Close</button>
          </div>
        </div>
      </div>
      <div class="flex-grow overflow-x-hidden overflow-y-scroll">
        <template v-if="data.length > 1">
          <div class="flex flex-wrap -mx-4 -mb-8">
            <template v-for="(item, key) in data">
              <existing-media-item :item="item" :key="key" v-on:select="$emit('select', item) && close()"></existing-media-item>
            </template>
          </div>
        </template>
        <template v-else>
          <h4 class="text-center mt-8">No results found</h4>
        </template>
        <template v-if="loading">
          <h4 class="text-center mt-8">Loading...</h4>
        </template>
      </div>
      <template v-if="showNextPage">
        <div class="flex-shrink border-t border-40 pt-4 mt-4 text-right">
          <button type="button" class="form-file-btn btn btn-default btn-primary ml-auto" v-on:click="nextPage">Load Next Page</button>
        </div>
      </template>
    </div>
  </div>
</template>

<script>
  import ExistingMediaItem from './ExistingMediaItem';
  import debounce from 'lodash/debounce';

  export default{
    components: {
      ExistingMediaItem
    },
    data () {
      return {
        requestParams: {
          search_text: '',
          page: 1,
          per_page: 18
        },
        data: [],
        response: {},
        loading: false,
        search: _.debounce (function () {
          this.refresh();
        }, 300),
      }
    },
    props: {
      open: {
        default: false,
        type: Boolean
      }
    },
    computed: {
      showNextPage () {
        if (this.data.length == (this.requestParams.page * this.requestParams.per_page)) {
          return true;
        }
        return false;
      }
    },
    methods: {
      close () {
        this.$emit('close');
      },
      refresh () {
        this.requestParams.page = 1;
        return this.fireRequest().then((response) => {
          this.data = response.data.data;
          return response;
        });
      },
      nextPage () {
        this.requestParams.page += 1;
        return this.fireRequest().then((response) => {
          this.data = this.data.concat(response.data.data);
          return response;
        });
      },
      fireRequest () {
        // Set loading to true
        this.loading = true;

        return this.createRequest().then(response => {
          this.response = response;
          return response;
        }).finally(() => {
          // Set loading to false
          this.loading = false;
        });
      },
      /**
       * Request builders the request
       */
      createRequest () {
        return Nova.request()
          .get(
            `/nova-vendor/ebess/advanced-nova-media-library/media`,
            {
              params: this.requestParams
            }
          )
      }
    },
    watch: {
      open: function (newValue) {
        if (newValue) {
          this.refresh();
        }
      }
    }
  }
</script>
