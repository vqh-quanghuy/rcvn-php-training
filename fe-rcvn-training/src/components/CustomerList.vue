<template>
  <div>
    <v-form ref="searchForm">
      <v-row>
        <v-col cols="6" sm="6" md="3" >
          <v-text-field
            v-model="searchItem.customer_name"
            label="Họ và tên"
          ></v-text-field>
        </v-col>
        <v-col cols="6" sm="6" md="3" >
          <v-text-field
            v-model="searchItem.customer_email"
            label="Email"
          ></v-text-field>
        </v-col>
        <v-col cols="6" sm="6" md="3" >
          <v-select
            :items="customerStatus"
            item-value="id"
            item-text="text"
            v-model="searchItem.customer_status"
            label="Trạng thái"
          ></v-select>
        </v-col>
        <v-col cols="6" sm="6" md="3" >
          <v-text-field
            v-model="searchItem.customer_address"
            label="Địa chỉ"
          ></v-text-field>
        </v-col>
      </v-row>
      <v-row class="flex-row-reverse">
        <v-btn @click="clearSearch()" small color="secondary" class="mr-4">Xóa tìm</v-btn>
        <v-btn @click="load()" small color="info" class="mr-4">Tìm kiếm</v-btn>
      </v-row>
    </v-form>
    <v-data-table
      :headers="headers"
      :items="customers.data"
      disable-sort
      class="elevation-2 mt-6"
      hide-default-footer
      disable-pagination
    >
      <template v-slot:top>
        <v-toolbar flat>
          <v-toolbar-title>Danh sách khách hàng</v-toolbar-title>
          <v-divider class="mx-4" inset vertical></v-divider>
          <v-spacer></v-spacer>
          <v-dialog v-model="dialog" max-width="600px">
            <template v-slot:activator="{ on, attrs }">
              <v-btn color="primary" dark class="mb-2" v-bind="attrs" v-on="on">
                Thêm mới
              </v-btn>
            </template>
            <v-card>
              <v-card-title>
                <span class="text-h5">{{ formTitle }}</span>
              </v-card-title>
              <v-alert
                class="mx-2 error--text"
                v-model="alert"
                dismissible
                color="error"
                border="left"
                elevation="1"
                colored-border
                icon="mdi-alert-outline"
                @input="closeAlert()"
              >
                <b>{{alertMesg}}</b>
              </v-alert>
              <v-card-text>
                <v-container>
                  <v-form ref="editedForm">
                    <v-row>
                      <v-col cols="12" sm="6" md="6">
                        <v-text-field
                          v-model="editedItem.customer_name"
                          label="Tên"
                          validate-on-blur
                          :rules="customerNameRules"
                          :error-messages="editedItemErrors.customer_name"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="12" sm="6" md="6">
                        <v-text-field
                          v-model="editedItem.email"
                          label="Email"
                          validate-on-blur
                          :rules="emailRules"
                          :error-messages="editedItemErrors.email"
                          @change="clearEmailErrors()"
                        ></v-text-field>
                      </v-col>
                      <template v-if="editedIndex === -1">
                        <v-col cols="12" sm="6" md="6">
                          <v-text-field
                            :append-icon="
                              showPassword ? 'mdi-eye' : 'mdi-eye-off'
                            "
                            :type="showPassword ? 'text' : 'password'"
                            class="input-group--focused"
                            @click:append="showPassword = !showPassword"
                            v-model="editedItem.password"
                            label="Password"
                            validate-on-blur
                            :rules="passwordRules"
                            :error-messages="editedItemErrors.password"
                          ></v-text-field>
                        </v-col>
                        <v-col cols="12" sm="6" md="6">
                          <v-text-field
                            :append-icon="
                              showPasswordConfirm ? 'mdi-eye' : 'mdi-eye-off'
                            "
                            :type="showPasswordConfirm ? 'text' : 'password'"
                            class="input-group--focused"
                            @click:append="
                              showPasswordConfirm = !showPasswordConfirm
                            "
                            v-model="editedItem.password_confirmation"
                            label="Password Confirm"
                            validate-on-blur
                            :rules="passwordConfirmRules"
                            :error-messages="
                              editedItemErrors.password_confirmation
                            "
                          ></v-text-field>
                        </v-col>
                      </template>
                      <v-col cols="12" sm="12" md="12">
                        <v-text-field
                          v-model="editedItem.tel_num"
                          label="Điện thoại"
                          validate-on-blur
                          :rules="telNumRules"
                          :error-messages="editedItemErrors.tel_num"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="12" sm="12" md="12">
                        <v-text-field
                          v-model="editedItem.address"
                          label="Địa chỉ"
                          validate-on-blur
                          :rules="addressRules"
                          :error-messages="editedItemErrors.address"
                        ></v-text-field>
                      </v-col>
                      <v-col cols="12" sm="6" md="6">
                        <v-checkbox
                          v-model="editedItem.is_active"
                          label="Trạng thái"
                          :error-messages="editedItemErrors.is_active"
                        ></v-checkbox>
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
        </v-toolbar>
      </template>
      <template v-slot:[`item.actions`]="{ item }">
        <v-icon medium class="mx-1" color="accent" @click="editItem(item)">mdi-pencil</v-icon>
      </template>
      <template v-slot:no-data>
        <v-btn color="primary" @click="load"> Tải lại </v-btn>
      </template>
      <template v-slot:[`item.index`]="{ index }">
        {{ index + 1 }}
      </template>
      <!-- <template v-slot:[`item.is_active`]="{ item }">
        <v-chip v-if="item.is_active === 1" color="green">
          <b>Hoạt động</b>
        </v-chip>
        <v-chip v-else color="red">
          <b>Đã khóa</b>
        </v-chip>
      </template> -->
    </v-data-table>
    <v-row class="text-center pt-4 align-center" wrap>
      <v-col class="text-truncate" cols="12" md="12">
        Hiển thị từ {{customers.from}} ~ {{customers.to}} trên tổng số {{ customers.total }} khách hàng
      </v-col>
    </v-row>
    <v-row class="text-center px-4 align-center" wrap>
      <v-col cols="12" md="7">
        <v-pagination v-model="page" :length="customers.last_page">
        </v-pagination>
      </v-col>
      <v-col cols="6" md="3">
        <v-select
          dense
          outlined
          hide-details
          :value="itemsPerPage"
          label="Số lượng hiển thị mỗi trang"
          @change="itemsPerPage = parseInt($event, 10)"
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
          min=1
          :max="customers.last_page"
          @input="page = parseInt($event, 10);
                  page = (page > customers.last_page) ? customers.last_page : (page < 1) ? 1 : page"
        ></v-text-field>
      </v-col>
    </v-row>
  </div>
</template>
<script>
export default {
  name: "CustomerList",
  data() {
    return {
      customers: {},
      dialog: false,
      alert: false,
      alertMesg: '',
      alertType: '',
      headers: [
        { text: "#", value: "index", width: "5%" },
        { text: "Họ tên", align: "start", value: "customer_name", width: "20%" },
        { text: "Email", value: "email", width: "20%" },
        { text: "Địa chỉ", value: "address", width: "40%" },
        { text: "Điện thoại", value: "tel_num", width: "20%" },
        // { text: "Tình trạng", value: "is_active", align: "center" },
        { text: "Hành động", value: "actions", align: "center", width: "20%" },
      ],
      page: 1,
      itemsPerPage: 10,
      perPageChoices: [
        { text: "5 khách hàng/ trang", value: 5 },
        { text: "10 khách hàng/ trang", value: 10 },
        { text: "20 khách hàng/ trang", value: 20 },
      ],
      editedIndex: -1,
      showPassword: false,
      showPasswordConfirm: false,
      editedItem: {
        customer_name: "",
        email: "",
        password: "",
        password_confirmation: "",
        address: "",
        tel_num: "",
        customer_id: "",
        is_active: false, //True: 1 => Active,  False: 0 => Inactive
      },
      defaultItem: {
        customer_name: "",
        email: "",
        password: "",
        password_confirmation: "",
        address: "",
        tel_num: "",
        customer_id: "",
        is_active: false, //True: 1 => Active,  False: 0 => Inactive
      },
      editedItemErrors: {
        customer_name: [],
        email: [],
        password: [],
        password_confirm: [],
        address: [],
        tel_num: [],
        is_active: [],
      },

      searchItem: {
        customer_name: '',
        customer_email: '',
        customer_status: '',
        customer_address: '',
      },

      customerStatus: [
        { id: 0, text: "Đã khóa" },
        { id: 1, text: "Hoạt động" },
      ],
      // Validation rule
      customerNameRules: [
        (v) => !!v || "Vui lòng nhập tên khách hàng.",
        (v) => (v && v.length <= 255) || "Tên khách hàng phải ít hơn 255 ký tự.",
        (v) => (v && v.length >= 5) || "Tên khách hàng phải lớn hơn 5 ký tự.",
      ],
      emailRules: [
        (v) => !!v || "Email không được để trống.",
        (v) =>
          (v && v.length <= 255) || "Email must be less than 255 characters.",
        (v) =>
          /^(([^<>()[\]\\.,;:\s@']+(\.[^<>()\\[\]\\.,;:\s@']+)*)|('.+'))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(
            v
          ) || "E-mail không đúng định dạng.",
      ],
      passwordRules: [
        (v) => !!v || "Nhập mật khẩu.",
        (v) =>
          (v && v.length >= 8) || "Mật khẩu phải lớn hơn 8 ký tự.",
      ],
      passwordConfirmRules: [
        (v) => !!v || "Xác nhận mật khẩu",
        (v) =>
          (v && v.length >= 8) || "Mật khẩu xác nhận phải lớn hơn 8 ký tự.",
        (v) =>
          v === this.editedItem.password || "Xác nhận mật khẩu không khớp.",
      ],
      addressRules: [
        (v) => !!v || "Địa chỉ không được để trống",
        (v) =>
          (v && v.length <= 255) ||
          "Địa chỉ phải ít hơn 255 ký tự.",
      ],
      telNumRules: [
        (v) => !!v || "Điện thoại không được để trống",
        (v) =>
          (v && v.length <= 14) ||
          "Số điện thoại phải ít hơn 14 số.",
      ],

      apiHeaders: {
        "Content-Type": "application/json",
        Accept: "application/json",
        Authorization: "Bearer " + sessionStorage.getItem("access_token"),
      },
    };
  },
  methods: {
    async load(page = 1, itemsPerPage = 10) {
      await this.$axios
        .get(`${this.$backendUrl}user/customers/`, {
          headers: this.apiHeaders,
          params: {
            page: page,
            per_page: itemsPerPage,
            name: this.searchItem.customer_name,
            email: this.searchItem.customer_email,
            customer_status: this.searchItem.customer_status,
            address: this.searchItem.customer_address,
          },
        })
        .then((res) => {
          if (res.status === 200) {
            const DATA = res.data.data;
            this.customers = DATA;
          }
        })
        .catch((err) => {
          if (err.status !== 200) console.error(err.response.data.message);
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

    close() {
      this.editedItemErrors = {};
      this.$refs.editedForm.reset();
      this.editedIndex = -1;
      this.alert = false;
      this.alertMesg = '';
      this.alertType = '';
      this.dialog = false;
    },

    async save() {
      if (!this.$refs.editedForm.validate()) return;

      let editedItem = this.editedItem;
      if (this.editedIndex > -1) {
        // Call to Edit axios
        await this.$axios
          .put(
            `${this.$backendUrl}user/customers/${editedItem.customer_id}`,
            {
              customer_name: editedItem.customer_name,
              email: editedItem.email,
              address: editedItem.address,
              tel_num: editedItem.tel_num,
              is_active: editedItem.is_active ? 1 : 0,
            },
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
            this.alertMesg = err.response.data.message;
            this.alert = true;
            this.alertType = 'error';
          });
      } else {
        // Call to Create axios
        await this.$axios
          .post(
            `${this.$backendUrl}user/customers`,
            {
              customer_name: editedItem.customer_name,
              email: editedItem.email,
              password: editedItem.password,
              password_confirmation: editedItem.password_confirmation,
              address: editedItem.address,
              tel_num: editedItem.tel_num,
              is_active: editedItem.is_active ? 1 : 0,
            },
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
            this.alertMesg = err.response.data.message;
            this.alert = true;
            this.alertType = 'error';
          });
      }
    },
    clearEmailErrors() {
      this.editedItemErrors.email = [];
    },
  },
  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "Thêm khách hàng" : "Chỉnh sửa khách hàng";
    },
  },

  watch: {
    dialog(val) {
      val || this.close();
    },
    page(val) {
      this.load(val, this.itemsPerPage);
    },
    itemsPerPage(val) {
      this.load(this.page, val);
    },
  },
  mounted() {
    this.load(this.page, this.itemsPerPage);
  },
};
</script>