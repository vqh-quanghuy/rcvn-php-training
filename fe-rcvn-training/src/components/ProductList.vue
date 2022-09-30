<template>
  <div>
    <!-- <v-row> -->
      <v-form ref="searchForm">
        <v-row>
          <v-col cols="6" sm="6" md="3" >
            <v-text-field
              v-model="searchItem.product_name"
              label="Tên sản phẩm"
            ></v-text-field>
          </v-col>
          <v-col cols="6" sm="6" md="3" >
            <v-select
              :items="productStatus"
              item-value="id"
              item-text="text"
              v-model="searchItem.is_sale"
              label="Trạng thái"
            ></v-select>
          </v-col>
          <v-col cols="6" sm="6" md="3" >
            <v-text-field
              v-model="searchItem.fromPrice"
              type="number"
              min="0"
              label="Giá bán từ"
            ></v-text-field>
          </v-col>
          <v-col cols="6" sm="6" md="3" >
            <v-text-field
              v-model="searchItem.toPrice"
              type="number"
              :min="searchItem.fromPrice"
              label="Giá bán đến"
            ></v-text-field>
          </v-col>
        </v-row>
        <v-row class="flex-row-reverse">
          <v-btn @click="clearSearch()" small color="secondary" class="mr-4">
            <v-icon left>
              mdi-sort-variant-remove
            </v-icon>
            Xóa tìm
          </v-btn>
          <v-btn @click="load()" small color="info" class="mr-4">
            <v-icon left>
              mdi-archive-search-outline
            </v-icon>
            Tìm kiếm
          </v-btn>
        </v-row>
      </v-form>
      <!-- </v-row> -->
    <v-data-table
      :headers="headers"
      :items="products.data"
      disable-sort
      class="elevation-2 mt-6"
      hide-default-footer
      disable-pagination
    >
      <template v-slot:top>
        <v-toolbar flat>
          <v-toolbar-title>Danh sách sản phẩm</v-toolbar-title>
          <v-divider class="mx-4" inset vertical></v-divider>
          <v-spacer></v-spacer>
          <v-dialog v-model="dialog" max-width="600px">
            <template v-slot:activator="{ on, attrs }">
              <v-btn color="primary" dark class="mb-2" v-bind="attrs" v-on="on">
                <v-icon left>
                  mdi-package-variant-closed-plus
                </v-icon>
                Thêm mới
              </v-btn>
            </template>
            <v-card>
              <v-card-title>
                <span class="text-h5">{{ formTitle }}</span>
              </v-card-title>

              <v-card-text>
                <v-container>
                  <v-form ref="editedForm">
                    <v-row>
                      <v-col cols="12" sm="6" md="6">
                        <v-text-field
                          v-model="editedItem.product_name"
                          label="Tên sản phẩm"
                          validate-on-blur
                          :rules="productNameRules"
                          :error-messages="editedItemErrors.product_name"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="12" sm="6" md="6">
                        <v-text-field
                          v-model="editedItem.product_price"
                          type="number"
                          min="0"
                          label="Giá bán"
                          validate-on-blur
                          :rules="productPriceRules"
                          :error-messages="editedItemErrors.product_price"
                        ></v-text-field>
                      </v-col>
                      <template v-if="editedIndex > -1">
                        <v-col cols="12" sm="12" md="12" >
                          <template 
                            v-if="editedItem.product_image !== null && editedItem.product_image.length > 0">
                            <v-row align="center">
                              <v-col cols="12" sm="12" md="12" >
                                <v-img
                                  max-height="200"
                                  max-width="200"
                                  class="mx-auto"
                                  :src="backendImageUrl+editedItem.product_image"
                                  v-if="!isRemovedImage"
                                ></v-img>
                              </v-col>
                            </v-row>
                            <v-row>
                              <v-col cols="12" sm="12" md="12" >
                                <v-checkbox
                                  v-model="isRemovedImage"
                                  label="Cập nhật ảnh"
                                  :error-messages="editedItemErrors.is_removed_image"
                                ></v-checkbox>
                              </v-col>
                            </v-row>
                          </template>
                          <v-file-input
                            accept="image/png, image/jpeg, image/jpg"
                            prepend-icon="mdi-camera"
                            v-if="isRemovedImage || editedItem.product_image === null || editedItem.product_image === ''"
                            label="Cập nhật hình ảnh sản phẩm"
                            id="product_image"
                            v-model="editedProductImage"
                            :error-messages="editedItemErrors.product_image"
                            show-size
                          ></v-file-input>
                        </v-col>
                      </template>
                      <template v-else>
                        <v-col cols="12" sm="12" md="12">
                          <v-file-input
                            accept="image/png, image/jpeg, image/jpg"
                            prepend-icon="mdi-camera"
                            label="Hình ảnh sản phẩm"
                            id="product_image"
                            v-model="editedItem.product_image"
                            :error-messages="editedItemErrors.product_image"
                            show-size
                          ></v-file-input>
                        </v-col>
                      </template>
                      <v-col cols="12" sm="12" md="12">
                        <v-textarea
                          v-model="editedItem.description"
                          label="Mô tả sản phẩm"
                          validate-on-blur
                          :error-messages="editedItemErrors.description"
                        ></v-textarea>
                      </v-col>
                      <v-col cols="12" sm="6" md="6">
                        <v-select
                          :items="productStatus"
                          item-value="id"
                          item-text="text"
                          v-model="editedItem.is_sale"
                          label="Trạng thái"
                          validate-on-blur
                          :rules="statusRules"
                          :error-messages="editedItemErrors.is_sale"
                        ></v-select>
                      </v-col>
                    </v-row>
                  </v-form>
                </v-container>
              </v-card-text>

              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="secondary" text @click="close">Hủy</v-btn>
                <v-btn color="primary" text @click="save">Lưu</v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>
          <v-dialog v-model="dialogDelete" max-width="600px">
            <v-card>
              <v-card-title class="text-h5"
                >Bạn có muốn xóa sản phẩm {{itemIdToDelete}} không</v-card-title
              >
              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="secondary" text @click="closeDelete"
                  >Hủy bỏ</v-btn
                >
                <v-btn color="primary" text @click="deleteItemConfirm"
                  >OK</v-btn
                >
                <v-spacer></v-spacer>
              </v-card-actions>
            </v-card>
          </v-dialog>
        </v-toolbar>
      </template>
      <template v-slot:[`item.actions`]="{ item }">
        <v-icon medium class="mx-1" color="accent" @click="editItem(item)"
          >mdi-pencil</v-icon
        >
        <v-icon medium color="error" @click="deleteItem(item)"
          >mdi-delete</v-icon
        >
      </template>
      <template v-slot:[`item.product_id`]="{ item }">
        <v-menu open-on-hover top offset-x v-if="item.product_image === null || item.product_image === ''">
          <template v-slot:activator="{ on, attrs }">
            <v-btn
              text
              v-bind="attrs"
              v-on="on"
            >
              {{item.product_id}}
            </v-btn>
          </template>
          <v-card>No image data</v-card>
        </v-menu>
        <v-menu open-on-hover top offset-x v-else>
          <template v-slot:activator="{ on, attrs }">
            <v-btn
              text
              v-bind="attrs"
              v-on="on"
              color="primary"
            >
              {{item.product_id}}
            </v-btn>
          </template>
          <v-img
            max-height="250"
            max-width="250"
            :src="backendImageUrl+item.product_image"
          ></v-img>
        </v-menu>
      </template>
      <template v-slot:no-data>
        <b>Không có dữ liệu</b><br>
        <v-btn small color="info" class="mb-2" @click="load"> 
          <v-icon left>
            mdi-reload
          </v-icon>
          Tải lại
        </v-btn>  
      </template>
      <template v-slot:[`item.index`]="{ index }">
        {{ index + 1 }}
      </template>
      <template v-slot:[`item.is_sale`]="{ item }">
        <v-chip v-if="item.is_sale === 2" color="orange">
          <b>Hết hàng</b>
        </v-chip>
        <v-chip v-else-if="item.is_sale === 1" color="green">
          <b>Đang bán</b>
        </v-chip>
        <v-chip v-else-if="item.is_sale === 0" color="red">
          <b>Ngừng bán</b>
        </v-chip>
      </template>
    </v-data-table>
    <v-row class="text-center pt-4 align-center" wrap>
      <v-col class="text-truncate" cols="12" md="12">
        Hiển thị từ {{products.from}} ~ {{products.to}} trên tổng số {{ products.total }} sản phẩm
      </v-col>
    </v-row>
    <v-row class="text-center px-4 align-center" wrap v-if="products.total > itemsPerPage">
      <v-col cols="12" md="7">
        <v-pagination v-model="page" :length="products.last_page">
        </v-pagination>
      </v-col>
      <v-col cols="6" md="3">
        <v-select
          dense
          outlined
          hide-details
          :value="itemsPerPage"
          label="Số lượng hiển thị mỗi trang"
          @change="itemsPerPage = parseInt($event, 10), page = 1"
          :items="perPageChoices"
        >
        </v-select>
      </v-col>
      <v-col cols="6" md="2">
        <v-text-field
          v-model="page"
          label="Đi tới trang"
          type="number"
          outlined
          hide-details
          dense
          min="1"
          :max="products.last_page"
          @input="
            page = parseInt($event, 10);
            page =
              page > products.last_page
                ? products.last_page
                : page < 1
                ? 1
                : page;
          "
        ></v-text-field>
      </v-col>
    </v-row>
  </div>
</template>
<script>
export default {
  name: "ProductList",
  data() {
    return {
      products: {},
      dialog: false,
      dialogDelete: false,
      dialogDeactivate: false,
      headers: [
        { text: "#", value: "index", width: "5%" },
        { text: "Mã sản phẩm", align: "middle", value: "product_id", width: "10%" },
        { text: "Tên sản phẩm", align: "start", value: "product_name", width: "20%" },
        { text: "Mô tả", value: "description", width: "35%" },
        { text: "Giá", value: "product_price", width: "5%" },
        { text: "Tình trạng", value: "is_sale", align: "center", width: "10%" },
        { text: "Hành động", value: "actions", align: "center", width: "30%" },
      ],
      page: 1,
      itemsPerPage: 10,
      perPageChoices: [
        { text: "5 sản phẩm/ trang", value: 5 },
        { text: "10 sản phẩm/ trang", value: 10 },
        { text: "20 sản phẩm/ trang", value: 20 },
      ],
      searchItem: {
        product_name: '',
        is_sale: '',
        fromPrice: '',
        toPrice: '',
      },
      isRemovedImage: false, // false 0 -> not remove, true 1 -> remove
      editedProductImage: [],
      editedIndex: -1,
      editedItem: {
        product_name: "",
        product_price: "",
        product_image: [],
        description: "",
        product_id: "",
        is_sale: 1, //True: 1 => Sale,  False: 0 => Out of stock
      },
      defaultItem: {
        product_name: "",
        product_price: "",
        product_image: [],
        description: "",
        product_id: "",
        is_sale: 1, //True: 1 => Sale,  False: 0 => Out of stock
      },
      productStatus: [
        { id: 1, text: 'Đang bán' },
        { id: 2, text: 'Hết hàng' },
        { id: 0, text: 'Ngừng bán' }
      ],
      editedItemErrors: {
        product_name: [],
        product_price: [],
        product_image: [],
        description: [],
        is_sale: [],
        is_removed_image: [],
      },
      itemIdToDelete: '',

      // Validation rule
      productNameRules: [
        (v) => !!v || "Tên sản phẩm không được để trống.",
        (v) =>
          (v && v.length <= 255) ||
          "Tên sản phẩm phải ít hơn 255 ký tự.",
      ],
      productPriceRules: [
        (v) => !!v || "Giá bán không được để trống.",
        (v) => /^\d*\.?\d*$/.test(v) || "Giá bán phải là số.",
      ],
      statusRules: [
        v => Number.isInteger(v!== null ? Number(v) : '') || 'Trạng thái không được để trống.',
      ],

      apiHeaders: {
        "Content-Type": "multipart/form-data",
        "Accept": "application/json",
        "Authorization": "Bearer " + sessionStorage.getItem("access_token"),
      },
      backendImageUrl: '',
    };
  },
  methods: {
    async load(page = 1, itemsPerPage = 10) {
      await this.$axios
        .get(`${this.$backendUrl}user/products/`, {
          headers: this.apiHeaders,
          params: {
            page: page,
            per_page: itemsPerPage,
            name: this.searchItem.product_name,
            sale_status: this.searchItem.is_sale,
            from_price: this.searchItem.fromPrice,
            to_price: this.searchItem.toPrice,
          },
        })
        .then((res) => {
          if (res.status === 200) {
            const DATA = res.data.data;
            this.products = DATA;
          }
        })
        .catch((err) => {
          if (err.status !== 200) {
            console.error(err.response.data.message)
            if(err.response.status === 401 && err.response.data.message === 'Unauthenticated.') {
              console.log('123 :>> ');
              this.$emit('unauthenticated')
            }
          }
        });
    },
    clearSearch() {
      this.$refs.searchForm.reset();
      this.load();
    },
    editItem(item) {
      this.editedIndex = 1;
      this.editedItem = Object.assign({}, item);
      this.dialog = true;
    },

    deleteItem(item) {
      this.itemIdToDelete = item.product_id;
      this.dialogDelete = true;
    },

    async deleteItemConfirm() {
      await this.$axios
        .delete(`${this.$backendUrl}user/products/${this.itemIdToDelete}`, {
          headers: this.apiHeaders,
        })
        .then((res) => {
          alert(res.data.message);
          this.load(this.page, this.itemsPerPage);
        })
        .catch((err) => {
          alert(err.response.data.message);
        });

      this.closeDelete();
    },

    close() {
      this.editedItemErrors = {};
      this.editedIndex = -1;
      this.$refs.editedForm.reset();
      this.dialog = false;
    },

    closeDelete() {
      this.dialogDelete = false;
      this.itemIdToDelete = "";
    },

    debug(msg) {
      console.log(msg);
    },

    async save() {
      if (!this.$refs.editedForm.validate()) return;

      let editedItem = this.editedItem;

      let formData = new FormData();
      formData.append('product_name', editedItem.product_name);
      formData.append('product_price', editedItem.product_price);
      formData.append('description', editedItem.description !== null ? editedItem.description : '');
      formData.append('is_sale', editedItem.is_sale);


      if (this.editedIndex > -1) {
        // Call to Edit axios
        formData.append("_method", "PUT");
        if(this.editedProductImage instanceof File) {
          formData.append('product_image', this.editedProductImage);
        } else {
          formData.append('product_image', '');
        }
        console.log('this.isRemovedImage :>> ', this.isRemovedImage);
        formData.append('is_removed_image', this.isRemovedImage ? 1 : 0);

        await this.$axios
          .post(
            `${this.$backendUrl}user/products/${editedItem.product_id}`,
            formData,
            {
              headers: this.apiHeaders,
            }
          )
          .then((res) => {
            alert(res.data.message);
            this.load(this.page, this.itemsPerPage);
            this.close();
          })
          .catch((err) => {
            this.editedItemErrors = err.response.data.error;
            alert(err.response.data.message);
          });
      } else {
        // Call to Create axios
        // console.log('editedItem.prouduct_image :>> ', editedItem.product_image);
        formData.append('product_image', editedItem.product_image ? editedItem.product_image : '');
        await this.$axios
          .post(
            `${this.$backendUrl}user/products`,
              formData,
            {
              headers: this.apiHeaders,
            }
          )
          .then((res) => {
            alert(res.data.message);
            this.load(this.page, this.itemsPerPage);
            this.close();
          })
          .catch((err) => {
            this.editedItemErrors = err.response.data.error;
            alert(err.response.data.message);
          });
      }
    },
    roleName(roleId) {
      return this.roles.find((x) => x.id === parseInt(roleId)).role_name;
    },
    clearEmailErrors() {
      this.editedItemErrors.email = [];
    },
  },
  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "Thêm sản phẩm" : "Chỉnh sửa sản phẩm";
    },
  },

  watch: {
    dialog(val) {
      val || this.close();
    },
    dialogDelete(val) {
      val || this.closeDelete();
    },
    page(val) {
      this.load(val, this.itemsPerPage);
    },
    itemsPerPage(val) {
      this.load(this.page, val);
    },
  },
  mounted() {
    this.backendImageUrl = this.$backendImageUrl
    this.load(this.page, this.itemsPerPage);
  },
};
</script>