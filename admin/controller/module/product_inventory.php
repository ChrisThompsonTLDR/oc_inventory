<?php
class ControllerModuleProductInventory extends Controller {
	private $error = array(); // This is used to set the errors, if any.

	public function index() {   // Default function
		$inventory = $this->db->query('
			SELECT
				pd.name,
				p.model,
				p.price,
				p.cost,
				p.status,
				p.quantity,
				GROUP_CONCAT(cd.name) AS `categories`
			FROM
				product AS p
				LEFT JOIN product_description AS pd ON (pd.product_id = p.product_id)
				LEFT JOIN product_to_category AS pc ON (pc.product_id = p.product_id)
				LEFT JOIN category_description AS cd ON (cd.category_id = pc.category_id)
			GROUP BY
				pd.product_id
			LIMIT
				1000000
		');

		if (!empty($inventory->rows)) {
			$fh = fopen('php://output', 'w');

		    // Start output buffering (to capture stream contents)
		    ob_start();

		    fputcsv($fh, array_flip($inventory->row));

		    // Loop over the * to export
		    foreach($inventory->rows as $row) {
		        fputcsv($fh, (array) $row);
		    }

		    // Get the contents of the output buffer
		    $string = ob_get_clean();
		    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		    header('Content-type: text/csv');
		    header('Content-Disposition: attachment; filename=inventory-' . date('Y-m-d H:i:s') . '.csv');
		    exit($string);
		}

		$this->redirect($_SERVER['HTTP_REFERER']);
	}

	/**
	* Adds the permissions needed to access this module to the current user
	*
	*/
	public function install(){
		$this->load->model('user/user_group');
		$this->model_user_user_group->addPermission($this->user->getId(), 'access', 'module/product_inventory');
		$this->model_user_user_group->addPermission($this->user->getId(), 'modify', 'module/product_inventory');
	}
}