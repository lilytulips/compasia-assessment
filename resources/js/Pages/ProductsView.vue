<template>
  <div class="container mx-auto px-4 py-8">
    <!-- Upload Message -->
    <div v-if="uploadMessage" :class="[
      'mb-4 p-4 rounded',
      uploadMessageType === 'success' ? 'bg-green-100 text-green-800 border border-green-400' : 'bg-red-100 text-red-800 border border-red-400'
    ]">
      {{ uploadMessage }}
    </div>

    <!-- File Upload Section -->
    <div class="mb-6 flex items-center gap-4">
      <div class="flex items-center gap-2">
        <input
          type="file"
          ref="fileInput"
          @change="handleFile"
          accept=".xls,.xlsx"
          class="hidden"
          id="file-input"
        />
        <label
          for="file-input"
          class="px-4 py-2 border border-gray-300 rounded cursor-pointer hover:bg-gray-50"
        >
          Choose File
        </label>
        <span class="text-gray-600">{{ fileName || 'No file chosen' }}</span>
      </div>
      <button
        @click="upload"
        :disabled="!file || uploading"
        class="px-6 py-2 bg-green-600 text-white rounded hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed flex items-center gap-2"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
        </svg>
        Upload
      </button>
    </div>

    <!-- Title and Search Section -->
    <div class="mb-6 flex items-center justify-between">
      <h1 class="text-2xl font-bold">Product Master List</h1>
      <div class="flex items-center gap-2">
        <input
          v-model="search"
          @keyup.enter="fetchProducts"
          @input="debounceSearch"
          type="text"
          placeholder="Search"
          class="px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-500"
        />
      </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              No.
            </th>
            <th
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
              @click="sortBy('product_id')"
            >
              <div class="flex items-center gap-1">
                Product ID
                <span v-if="sortColumn === 'product_id'" class="text-green-600">
                  {{ sortOrder === 'asc' ? '↑' : '↓' }}
                </span>
                <span v-else class="text-gray-400">⇅</span>
              </div>
            </th>
            <th
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
              @click="sortBy('types')"
            >
              <div class="flex items-center gap-1">
                Types
                <span v-if="sortColumn === 'types'" class="text-green-600">
                  {{ sortOrder === 'asc' ? '↑' : '↓' }}
                </span>
                <span v-else class="text-gray-400">⇅</span>
              </div>
            </th>
            <th
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
              @click="sortBy('brand')"
            >
              <div class="flex items-center gap-1">
                Brand
                <span v-if="sortColumn === 'brand'" class="text-green-600">
                  {{ sortOrder === 'asc' ? '↑' : '↓' }}
                </span>
                <span v-else class="text-gray-400">⇅</span>
              </div>
            </th>
            <th
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
              @click="sortBy('model')"
            >
              <div class="flex items-center gap-1">
                Model
                <span v-if="sortColumn === 'model'" class="text-green-600">
                  {{ sortOrder === 'asc' ? '↑' : '↓' }}
                </span>
                <span v-else class="text-gray-400">⇅</span>
              </div>
            </th>
            <th
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
              @click="sortBy('capacity')"
            >
              <div class="flex items-center gap-1">
                Capacity
                <span v-if="sortColumn === 'capacity'" class="text-green-600">
                  {{ sortOrder === 'asc' ? '↑' : '↓' }}
                </span>
                <span v-else class="text-gray-400">⇅</span>
              </div>
            </th>
            <th
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
              @click="sortBy('quantity')"
            >
              <div class="flex items-center gap-1">
                Quantity
                <span v-if="sortColumn === 'quantity'" class="text-green-600">
                  {{ sortOrder === 'asc' ? '↑' : '↓' }}
                </span>
                <span v-else class="text-gray-400">⇅</span>
              </div>
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="(product, index) in products" :key="product.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ getRowNumber(index) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ product.product_id }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ product.types }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ product.brand }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ product.model }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ product.capacity }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ product.quantity }}
            </td>
          </tr>
          <tr v-if="products.length === 0">
            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
              No products found
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination Section -->
    <div class="mt-4 flex items-center justify-between">
      <div class="text-sm text-gray-700">
        Showing {{ pagination.from || 0 }} to {{ pagination.to || 0 }} of {{ pagination.total || 0 }} entries
      </div>
      <div class="flex items-center gap-2">
        <button
          @click="changePage(pagination.prev_page_url)"
          :disabled="!pagination.prev_page_url"
          class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-50 disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed"
        >
          Previous
        </button>
        <button
          v-if="pagination.current_page"
          class="px-4 py-2 bg-green-600 text-white rounded"
        >
          {{ pagination.current_page }}
        </button>
        <button
          @click="changePage(pagination.next_page_url)"
          :disabled="!pagination.next_page_url"
          class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-50 disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed"
        >
          Next
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      products: [],
      pagination: {},
      search: '',
      perPage: 10,
      file: null,
      fileName: '',
      uploading: false,
      sortColumn: 'id',
      sortOrder: 'asc',
      searchTimeout: null,
      uploadMessage: '',
      uploadMessageType: '', // 'success' or 'error'
      pollingInterval: null,
    };
  },
  mounted() {
    this.fetchProducts();
  },
  methods: {
    async fetchProducts(url = null) {
      try {
        let params = { per_page: this.perPage };

        if (this.search) {
          params.product_id = this.search;
        }

        params.sort_by = this.sortColumn;
        params.sort_order = this.sortOrder;

        const res = await axios.get(url || '/api/products', { params });
        this.products = res.data.data;
        this.pagination = {
          current_page: res.data.current_page,
          last_page: res.data.last_page,
          from: res.data.from,
          to: res.data.to,
          total: res.data.total,
          prev_page_url: res.data.prev_page_url,
          next_page_url: res.data.next_page_url,
        };
      } catch (e) {
        console.error(e);
        alert('Failed to fetch products');
      }
    },
    changePage(url) {
      if (!url) return;
      this.fetchProducts(url);
    },
    handleFile(e) {
      this.file = e.target.files[0];
      this.fileName = this.file ? this.file.name : '';
    },
    async upload() {
      if (!this.file) {
        this.showMessage('Please select a file', 'error');
        return;
      }

      this.uploading = true;
      this.uploadMessage = '';
      let form = new FormData();
      form.append('file', this.file);

      // Store current quantities for comparison
      const currentQuantities = {};
      this.products.forEach(p => {
        currentQuantities[p.product_id] = p.quantity;
      });

      try {
        await axios.post('/api/products/upload', form, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });

        this.showMessage('File uploaded. Processing...', 'success');

        // Poll for updates - check every 2 seconds for up to 30 seconds
        let attempts = 0;
        const maxAttempts = 15;

        this.pollingInterval = setInterval(async () => {
          attempts++;

          try {
            await this.fetchProducts();

            // Check if quantities have changed
            let hasChanges = false;
            this.products.forEach(p => {
              if (currentQuantities[p.product_id] !== undefined &&
                  currentQuantities[p.product_id] !== p.quantity) {
                hasChanges = true;
              }
            });

            if (hasChanges || attempts >= maxAttempts) {
              clearInterval(this.pollingInterval);
              this.pollingInterval = null;

              if (hasChanges) {
                this.showMessage('Products have been updated successfully!', 'success');
              } else {
                // Max attempts reached but no changes detected
                this.showMessage('Processing completed. No quantity changes detected.', 'success');
              }
              this.uploading = false;
            }
          } catch (err) {
            console.error('Error polling for updates:', err);
            if (attempts >= maxAttempts) {
              clearInterval(this.pollingInterval);
              this.pollingInterval = null;
              this.uploading = false;
            }
          }
        }, 2000);

        // Reset file input
        this.file = null;
        this.fileName = '';
        if (this.$refs.fileInput) {
          this.$refs.fileInput.value = '';
        }
      } catch (err) {
        console.error(err);
        this.showMessage('Upload failed: ' + (err.response?.data?.message || err.message), 'error');
        this.uploading = false;
        if (this.pollingInterval) {
          clearInterval(this.pollingInterval);
          this.pollingInterval = null;
        }
      }
    },
    showMessage(message, type) {
      this.uploadMessage = message;
      this.uploadMessageType = type;

      // Auto-hide success messages after 5 seconds
      if (type === 'success') {
        setTimeout(() => {
          if (this.uploadMessage === message) {
            this.uploadMessage = '';
            this.uploadMessageType = '';
          }
        }, 5000);
      }
    },
    sortBy(column) {
      if (this.sortColumn === column) {
        this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
      } else {
        this.sortColumn = column;
        this.sortOrder = 'asc';
      }
      this.fetchProducts();
    },
    debounceSearch() {
      clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => {
        this.fetchProducts();
      }, 500);
    },
    getRowNumber(index) {
      if (!this.pagination.current_page) return index + 1;
      return (this.pagination.current_page - 1) * this.perPage + index + 1;
    },
  },
};
</script>

<style scoped>
/* Additional custom styles if needed */
</style>
