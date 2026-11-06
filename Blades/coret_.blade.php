      <!-- PROVINSI -->
        <div>
          <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full mt-3"
            :value="values.province_id" @input="v=>values.province_id=v" :errorText="formErrors.province_id?'failed':''"
            @update:valueFull="(objVal)=>{
                    values.city_id = '',
                    values.district_id = '',
                    values.kode_pos = ''
                  }" :hints="formErrors.province_id" label="Provinsi" placeholder="Pilih Provinsi" valueField="id"
            displayField="value" :api="{
                    url: `${store.server.url_backend}/operation/m_general`,
                    headers: {
                      'Content-Type': 'Application/json',
                      Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                    params: {
                      simplest: true,
                      transform: false,
                      scopes: 'genProvinsi',
                      join: true,
                      selectfield: 'this.id, this.code, this.value, this.is_active'
                    }
                  }" :check="false" />

        </div>
      <!-- KOTA -->
      <div>
          <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full mt-3" :value="values.kota_id"
            @input="v=>values.city_id=v" :errorText="formErrors.city_id?'failed':''" @update:valueFull="(objVal)=>{
                    values.district_id = '',
                    values.kode_pos = ''
                  }" :hints="formErrors.city_id" label="Kota" placeholder="Pilih Kota" valueField="id" displayField="value"
            :api="{
                    url: `${store.server.url_backend}/operation/m_general`,
                    headers: {
                      'Content-Type': 'Application/json',
                      Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                    params: {
                      simplest: true,
                      transform: false,
                      scopes: 'genKota',
                      provinsi_id: values.province_id ?? null,
                      join: true,
                      selectfield: 'this.id, this.code, this.value, this.is_active'
                    }
                  }" :check="false" />
      </div>
      <!-- KECAMATAN -->
      <div>
          <FieldSelect :bind="{ disabled: !actionText, clearable:false }" class="w-full mt-3"
            :value="values.district_id" @input="v=>values.district_id=v"
            :errorText="formErrors.district_id?'failed':''" @update:valueFull="(objVal)=>{
                    values.kode_pos = ''
                  }" :hints="formErrors.district_id" label="Kecamatan" placeholder="Pilih Kecamatan" valueField="id"
            displayField="value" :api="{
                    url: `${store.server.url_backend}/operation/m_general`,
                    headers: {
                      'Content-Type': 'Application/json',
                      Authorization: `${store.user.token_type} ${store.user.token}`
                    },
                    params: {
                      simplest: true,
                      transform: false,
                      scopes: 'genKecamatan',
                      kota_id: values.city_id ?? null,
                      join: true,
                      selectfield: 'this.id, this.code, this.value, this.is_active'
                    }
                  }" :check="false" />

      </div>