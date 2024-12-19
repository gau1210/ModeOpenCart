<?php
// Caminho para chegar no arquivo dentro da pasta principal do OpenCard: catalog/controller/account/register.php

Subistitua o codigo abaixo no local que tem a // Custom field validation antes do if.

$this->load->model('account/custom_field');

$custom_fields = $this->model_account_custom_field->getCustomFields($customer_group_id);

foreach ($custom_fields as $custom_field) {
	if ($custom_field['location'] == 'account') {
		if ($custom_field['required'] && empty($this->request->post['custom_field'][$custom_field['custom_field_id']])) {
			$json['error']['custom_field_' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
		} elseif (($custom_field['type'] == 'text') && !empty($custom_field['validation']) && !preg_match(html_entity_decode($custom_field['validation'], ENT_QUOTES,
'UTF-8'), $this->request->post['custom_field'][$custom_field['custom_field_id']])) {
		$json['error']['custom_field_' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_regex'), $custom_field['name']);
		} 
			// Verifica duplicidade de CPF/CNPJ
			elseif ($custom_field['name'] == 'CPF/CNPJ') {
			// Recupera o CPF ou CNPJ diretamente do input, sem limpar os pontos e traços
			$cpf_cnpj = $this->request->post['custom_field'][$custom_field['custom_field_id']];
						
			$this->load->model('account/customer');
						
			// Escapa o valor para evitar injeção de SQL
			$cpf_cnpj = $this->db->escape($cpf_cnpj);

			// Consulta para verificar se o CPF já existe. A consulta vai verificar se já existe algum cliente com o mesmo CPF na chave "4" do campo JSON.
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE JSON_EXTRACT(custom_field, '$.\"4\"') = '" . $cpf_cnpj . "' LIMIT 1");

			// Se encontrar um CPF já cadastrado, retorna erro
			if ($query->num_rows) {
				$json['error']['custom_field_' . $custom_field['custom_field_id']] = $this->language->get('error_cpf_exists');
			}
		}
	}
}
?>