<template>
  <!-- Modal -->
  <Modal
    :show="open"
    maxWidth=""
    class="px-4"
  >
    <card class="overflow-hidden">

      <!-- Modal Content -->
      <div class="flex flex-col p-4 h-full relative w-full">
        <!-- Header bar -->
        <div class="border-b dark:border-gray-700 pb-3 mb-4 flex items-center">
          <!-- Heading -->
          <div class="px-4 self-center">
            <h3>{{ __('Existing Media') }}</h3>
          </div>

          <!-- Search -->
          <div class="px-4 self-center">
            <div class="relative">
              <icon type="search" class="inline-block absolute ml-2 text-gray-400" width="20" style="top:4px;"/>
              <input type="search"
                     v-bind:placeholder="__('Search by name or file name')"
                     class="appearance-none rounded-full h-8 pl-10 w-full bg-gray-100 dark:bg-gray-800 focus:bg-white focus:outline-none focus:ring"
                     v-model="requestParams.search_text"
                     @input="search"
                     @change="search"
                     @keydown.enter.prevent="search">
            </div>
          </div>

          <!-- Close -->
          <div class="px-4 ml-auto self-center">
            <OutlineButton type="button" @click="close">{{ __('Close') }}</OutlineButton>
          </div>
        </div>


        <div class="flex-grow overflow-x-hidden overflow-y-scroll" style="max-height: 79vh;">
          <!-- When we have results show them -->
          <div class="my-4 flex justify-center flex-wrap" v-if="items.length > 0">
            <existing-media-item v-for="(item, key) in items" :key="key" :item="item" @select="$emit('select', item); close()"></existing-media-item>
          </div>

          <!-- Show "Loading" or "No Results Found" text -->
          <h4 class="text-center m-8" v-if="loading">{{ __('Loading...') }}</h4>
          <h4 class="text-center m-8" v-else-if="items.length == 0">{{ __('No results found') }}</h4>
        </div>

        <!-- Next page -->
        <div class="flex-shrink border-t dark:border-gray-700 pt-3 mt-4 text-right" v-if="showNextPage">
          <DefaultButton type="button" class="ml-auto" @click="nextPage">{{ __('Load Next Page') }}</DefaultButton>
        </div>
      </div>
    </card>
  </Modal>
</template>

<script>
import ExistingMediaItem from './ExistingMediaItem';
import debounce from 'lodash/debounce';

export default {
  components: {
    ExistingMediaItem
  },
  data() {
    let aThis = this;
    return {
      requestParams: {
        search_text: '',
        page: 1,
        per_page: 18
      },
      items: [],
      response: {},
      loading: false,
      search: debounce(function () {
        aThis.refresh();
      }, 750),
    }
  },
  props: {
    open: {
      default: false,
      type: Boolean
    }
  },
  computed: {
    showNextPage() {
      if (this.items.length == (this.requestParams.page * this.requestParams.per_page)) {
        return true;
      }
      return false;
    }
  },
  methods: {
    close() {
      this.$emit('close');
    },
    refresh() {
      this.requestParams.page = 1;
      return this.fireRequest().then((response) => {
        this.items = response.data.data;
        return response;
      });
    },
    nextPage() {
      this.requestParams.page += 1;
      return this.fireRequest().then((response) => {
        this.items = this.items.concat(response.data.data);
        return response;
      });
    },
    fireRequest() {
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
    createRequest() {
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
        if (this.items.length == 0) {
          this.refresh();
        }
        document.body.classList.add('overflow-x-hidden');
        document.body.classList.add('overflow-y-hidden');
      } else {
        document.body.classList.remove('overflow-x-hidden');
        document.body.classList.remove('overflow-y-hidden');
      }
    }
  }
}
</script>
