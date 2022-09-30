<template>
  <div>
    <v-form ref="searchForm">
      <v-row>
        <v-col cols="6" sm="6" md="3" >
          <v-text-field
            v-model="searchItem.user_name"
            label="Họ và tên"
          ></v-text-field>
        </v-col>
        <v-col cols="6" sm="6" md="3" >
          <v-text-field
            v-model="searchItem.user_email"
            label="Email"
          ></v-text-field>
        </v-col>
        <v-col cols="6" sm="6" md="3" >
          <v-select
            :items="roles"
            item-value="id"
            item-text="role_name"
            v-model="searchItem.user_group_role"
            label="Nhóm"
          ></v-select>
        </v-col>
        <v-col cols="6" sm="6" md="3" >
          <v-select
            :items="userStatus"
            item-value="id"
            item-text="text"
            v-model="searchItem.user_status"
            label="Trạng thái"
          ></v-select>
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
    <v-data-table
      :headers="headers"
      :items="users.data"
      disable-sort
      class="elevation-2 mt-6"
      hide-default-footer
      disable-pagination
    >
      <template v-slot:top>
        <v-toolbar
          flat
        >
        <v-toolbar-title>Danh sách User</v-toolbar-title>
        <v-divider
          class="mx-4"
          inset
          vertical
        ></v-divider>
        <v-spacer></v-spacer>
        <v-dialog
          v-model="dialog"
          max-width="600px"
        >
          <template v-slot:activator="{ on, attrs }">
          <v-btn
            color="primary"
            dark
            class="mb-2"
            v-bind="attrs"
            v-on="on"
          >
            <v-icon left>
              mdi-account-plus
            </v-icon>
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
                      v-model="editedItem.name"
                      label="Tên"
                      validate-on-blur
                      :rules="nameRules"
                      :error-messages="editedItemErrors.name"
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
                        :append-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
                        :type="showPassword ? 'text' : 'password'"
                        class="input-group--focused"
                        @click:append="showPassword = !showPassword"
                        v-model="editedItem.password"
                        label="Mật khẩu"
                        validate-on-blur
                        :rules="passwordRules"
                        :error-messages="editedItemErrors.password"
                      ></v-text-field>
                    </v-col>
                    <v-col cols="12" sm="6" md="6">
                      <v-text-field
                        :append-icon="showPasswordConfirm ? 'mdi-eye' : 'mdi-eye-off'"
                        :type="showPasswordConfirm ? 'text' : 'password'"
                        class="input-group--focused"
                        @click:append="showPasswordConfirm = !showPasswordConfirm"
                        v-model="editedItem.password_confirmation"
                        label="Xác nhận mật khẩu"
                        validate-on-blur
                        :rules="passwordConfirmRules"
                        :error-messages="editedItemErrors.password_confirmation"
                      ></v-text-field>
                    </v-col>
                  </template>
                  <v-col cols="12" sm="12" md="12">
                    <v-select
                      :items="roles"
                      item-value="id"
                      item-text="role_name"
                      v-model="editedItem.group_role"
                      label="Nhóm"
                      validate-on-blur
                      :rules="groupRoleRules"
                      :error-messages="editedItemErrors.group_role"
                    ></v-select>
                  </v-col>
                  <v-col cols="12" sm="6" md="6">
                    <v-checkbox
                      v-model="editedItem.is_active"
                      input-value="true"
                      label="Trạng thái"
                      :error-messages="editedItemErrors.is_active"
                    ></v-checkbox>
                  </v-col>
                  <!-- <v-col cols="12" sm="6" md="6">
                    <v-checkbox
                      v-model="editedItem.is_delete"
                      label="Delete User"
                      :error-messages="editedItemErrors.is_delete"
                    ></v-checkbox>
                  </v-col> -->
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
          <v-card-title style="word-break: break-word" class="text-h5">Bạn có muốn xóa thành viên {{itemNameToDelete}} không ?</v-card-title>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="secondary" text @click="closeDelete">Hủy bỏ</v-btn>
            <v-btn color="primary" text @click="deleteItemConfirm">OK</v-btn>
            <v-spacer></v-spacer>
          </v-card-actions>
          </v-card>
        </v-dialog>
        <v-dialog v-model="dialogDeactivate" max-width="600px">
          <v-card>
          <v-card-title style="word-break: break-word" class="text-h5">Bạn có muốn {{ inactiveStatus ? 'khóa' : 'mở khóa' }} thành viên {{itemNameToDeactivate}} không ?</v-card-title>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="secondary" text @click="closeDeactivate">Cancel</v-btn>
            <v-btn color="primary" text @click="deactivateUserConfirm">OK</v-btn>
            <v-spacer></v-spacer>
          </v-card-actions>
          </v-card>
        </v-dialog>
        </v-toolbar>
      </template>
      <template v-slot:[`item.actions`]="{ item }">
        <v-icon medium class="mx-1" color="accent" @click="editItem(item)">mdi-pencil</v-icon>
        <v-icon medium class="mx-1" color="warning" @click="deactivateUser(item)">{{ item.is_active === 1 ? 'mdi-account-lock' : 'mdi-account-lock-open' }}</v-icon>
        <v-icon medium color="error" @click="deleteItem(item)">mdi-delete</v-icon>
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
      <template v-slot:[`item.group_role`]="{ item }">
        <p> {{ roleName(item.group_role) }}</p>
      </template>
      <template v-slot:[`item.is_active`]="{ item }">
        <v-chip v-if="item.is_active === 1" color="green">
          <b>Đang hoạt động</b>
        </v-chip>
        <v-chip v-else color="red">
          <b>Tạm khóa</b>
        </v-chip>
      </template>
    </v-data-table>
    <v-row class="text-center pt-4 align-center" wrap>
      <v-col class="text-truncate" cols="12" md="12">
        Hiển thị từ {{users.from}} ~ {{users.to}} trên tổng số {{ users.total }} users
      </v-col>
    </v-row>
    <v-row class="text-center px-4 align-center" wrap v-if="users.total > itemsPerPage">
      <v-col cols="12" md="7">
        <v-pagination
          v-model="page"
          :length="users.last_page">
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
          :items="perPageChoices">
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
          :max="users.last_page"
          @input="page = parseInt($event, 10); 
              page = (page > users.last_page) ? users.last_page : (page < 1) ? 1 : page"
        ></v-text-field>
      </v-col>
    </v-row>
  </div>
</template>
<script>
export default {
  name: 'UserList',
  data() {
    return {
      roles: [],
      users: {},
      dialog: false,
      dialogDelete: false,
      dialogDeactivate: false,
      alert: false,
      alertMesg: '',
      alertType: '',
      headers: [
        { text: '#', value: 'index', width: '5%'},
        { text: 'Họ tên', align: 'start', value: 'name', width: '20%'},
        { text: 'Email', value: 'email', width: '20%' },
        { text: 'Nhóm', value: 'group_role', width: '20%' },
        { text: 'Trạng thái', value: 'is_active', align: 'center', width: '20%' },
        { text: 'Hành động', value: 'actions', align: 'center', width: '20%' },
      ],
      page: 1,
      itemsPerPage: 10,
      perPageChoices: [
        {text:'5 Users/trang' , value: 5},
        {text:'10 Users/trang' , value: 10},
        {text:'20 Users/trang' , value: 20},
      ],
      editedIndex: -1,
      showPassword: false,
      showPasswordConfirm: false,
      editedItem: {
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        group_role: '',
        id: '',
        is_active: false, //True: 1 => Active,  False: 0 => Inactive
      },
      defaultItem: {
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        group_role: '',
        id: '',
        is_active: false, //True: 1 => Active,  False: 0 => Inactive
      },
      editedItemErrors: {
        name: [],
        email: [],
        password: [],
        password_confirm: [],
        group_role: [],
        is_active: [],
      },

      searchItem: {
        user_name: '',
        user_email: '',
        user_status: '',
        user_group_role: '',
      },

      userStatus: [
        { id: 0, text: "Tạm khóa" },
        { id: 1, text: "Đang hoạt động" },
      ],

      inactiveStatus: false,
      itemIdToDeactivate: '', 
      itemNameToDeactivate: '',
      itemIdToDelete: '', 
      itemNameToDelete: '', 

      // Validation rule
      nameRules: [
        v => !!v || 'Vui lòng nhập tên người sử dụng.',
        v => (v && v.length <= 255) || 'Tên phải ít hơn 255 ký tự.',
        v => (v && v.length >= 5) || 'Tên phải lớn hơn 5 ký tự.',
      ],
      emailRules: [
        v => !!v || 'Email không được để trống.',
        v => (v && v.length <= 255) || 'Email phải ít hơn 255 ký tự.',
        v => /^(([^<>()[\]\\.,;:\s@']+(\.[^<>()\\[\]\\.,;:\s@']+)*)|('.+'))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(v) || 'Email không đúng định dạng.',
      ], 
      passwordRules: [
        v => !!v || 'Mật khẩu không được để trống.',
        v => (v && v.length >= 8) || 'Mật khẩu phải lớn hơn 8 ký tự.',
        v => (v && /\d/.test(v)) || 'Mật khẩu phải có ít nhất 1 ký tự số.',
        v => (v && /[A-Z]{1}/.test(v)) || 'Mật khẩu phải có ít nhất 1 ký tự in hoa.',
        v => (v && /[a-z]{1}/.test(v)) || 'Mật khẩu phải có ít nhất 1 ký tự in thường.',
        v => (v && /[^A-Za-z0-9]/.test(v)) || 'Mật khẩu phải có ít nhất 1 ký tự đặc biệt.',
      ],
      passwordConfirmRules: [
        v => !!v || 'Xác nhận mật khẩu không được để trống.',
        v => (v && v.length >= 8) || 'Xác nhận mật khẩu phải lớn hơn 8 ký tự.',
        v => v === this.editedItem.password || 'Mật khẩu và xác nhận mật khẩu không chính xác.',
      ],
      groupRoleRules: [
        v => !!v || 'Nhóm phải được chọn.',
      ],

      apiHeaders: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': 'Bearer ' + sessionStorage.getItem("access_token")
      },
    }
  },
  methods: {
    async load(page = 1, itemsPerPage = 10) {
      await this.$axios
      .get(`${this.$backendUrl}user/users/`, {
        headers: this.apiHeaders,
        params: {
          page: page,
          per_page: itemsPerPage,
          name: this.searchItem.user_name,
          email: this.searchItem.user_email,
          user_status: this.searchItem.user_status,
          user_group_role: this.searchItem.user_group_role,
        }
      })
      .then(res => {
        if (res.status === 200) {
          const DATA = res.data.data;
          this.users = DATA;
          this.roles = res.data.roles;
        }
      })
      .catch(err => {
        if (err.status !== 200) {
          console.error(err.response.data.message)
          if(err.response.status === 401 && err.response.data.message === 'Unauthenticated.') {
            this.$emit('unauthenticated')
          }
        }
      })
    },

    clearSearch() {
      this.$refs.searchForm.reset();
      this.load();
    },

    editItem (item) {
      this.editedIndex = 1;
      item.group_role = parseInt(item.group_role);
      this.editedItem = Object.assign({}, item);
      this.dialog = true;
    },

    deleteItem (item) {
      this.itemIdToDelete = item.id;
      this.itemNameToDelete = item.name
      this.dialogDelete = true;
    },

    async deleteItemConfirm () {
      await this.$axios
      .delete(`${this.$backendUrl}user/users/${this.itemIdToDelete}`, {
        headers: this.apiHeaders
      })
      .then(res => {
        alert(res.data.message);
        this.load(this.page, this.itemsPerPage);
      })
      .catch(err => {
        alert(err.response.data.message);
      })

      this.closeDelete();
    },

    close () {
      this.editedItemErrors = {};
      this.$refs.editedForm.reset();
      this.editedIndex = -1;
      this.alert = false;
      this.alertMesg = '';
      this.alertType = '';
      this.dialog = false;
    },

    closeDelete () {
      this.dialogDelete = false;
      this.itemIdToDelete = '';
      this.itemNameToDelete = '';
    },

    deactivateUser(item) {
      this.itemIdToDeactivate = item.id;
      this.itemNameToDeactivate = item.name;
      this.dialogDeactivate = true;
      if(item.is_active === 1) this.inactiveStatus = true;
    },

    async deactivateUserConfirm() {
      await this.$axios
      .put(`${this.$backendUrl}user/users/deactivate/${this.itemIdToDeactivate}`, {} ,{
        headers: this.apiHeaders
      })
      .then(res => {
        alert(res.data.message);
        this.load(this.page, this.itemsPerPage);
      })
      .catch(err => {
        alert(err.response.data.message);
      })

      this.closeDeactivate();
    },

    closeDeactivate () {
      this.dialogDeactivate = false;
      this.itemIdToDeactivate = '';
      this.itemNameToDeactivate = '';
      this.inactiveStatus = false;
    },

    async save () {
      if(!this.$refs.editedForm.validate()) return;

      let editedItem = this.editedItem;
      if (this.editedIndex > -1) {
        // Call to Edit axios
        await this.$axios
        .put(`${this.$backendUrl}user/users/${editedItem.id}`, {
          name: editedItem.name,
          email: editedItem.email,
          group_role: editedItem.group_role.toString(),
          is_active: editedItem.is_active ? 1 : 0,
        }, {
          headers: this.apiHeaders
        })
        .then(res => {
          // this.alertMesg = res.data.message;
          // this.alert = true;
          // this.alertType = 'success';
          alert(res.data.message);
          this.load(this.page, this.itemsPerPage);
          this.close();
        })
        .catch(err => {
          this.editedItemErrors = err.response.data.error;
          this.alertMesg = err.response.data.message;
          this.alert = true;
          this.alertType = 'error';
        })
      } else {
        // Call to Create axios
        await this.$axios
        .post(`${this.$backendUrl}user/users`, {
          name: editedItem.name,
          email: editedItem.email,
          password: editedItem.password,
          password_confirmation: editedItem.password_confirmation,
          group_role: editedItem.group_role.toString(),
          is_active: editedItem.is_active ? 1 : 0,
        }, {
          headers: this.apiHeaders
        })
        .then(res => {
          // this.alertMesg = res.data.message;
          // this.alert = true;
          // this.alertType = 'success';
          alert(res.data.message);
          this.load(this.page, this.itemsPerPage);
          this.close();
        })
        .catch(err => {
          this.editedItemErrors = err.response.data.error;
          this.alertMesg = err.response.data.message;
          this.alert = true;
          this.alertType = 'error';
        })
      }
    },
    roleName(roleId) {
      return this.roles.find(x => x.id === parseInt(roleId)).role_name;
    },
    clearEmailErrors() {
      this.editedItemErrors.email = [];
    },
    closeAlert() {
      this.alert = false;
      this.alertMesg = '';
      this.alertType = '';
    }
  },
  computed: {
    formTitle () {
      return this.editedIndex === -1 ? 'Thêm User' : 'Chỉnh sửa User'
    },
  },

  watch: {
    dialog (val) {
      val || this.close()
    },
    dialogDelete (val) {
      val || this.closeDelete()
    },
    page(val) {
      this.load(val, this.itemsPerPage);
    },
    itemsPerPage(val) {
      this.load(this.page, val)
    }
  },
  mounted() {
    this.load(this.page, this.itemsPerPage);
  }
}
</script>