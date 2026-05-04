<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
	Class Name 		: 	My Model
	
	Created By		: 	Bhagwan Sahane
	Created Date 	: 	02-12-2015
	
	Updated By		:	Deepak Kanmahale
	Updated Date	:	06-06-2019
	
*/
class MY_Model extends CI_Model
{
    public function __construct()
    {
		parent::__construct();
	}
	//add by Deepak //get form data in array
	public function array_from_post($fields)
    {
        $data = array();
        foreach ($fields as $field) {
			if(is_array($this->input->post($field))){
				$data[$field] = $this->input->post($field);
			}else{
				$data[$field] = ($this->input->post($field) ? trim($this->input->post($field)) : NULL);
			}
            //$data[$field] = $this->input->post($field);
        }
        return $data;
    }

    public function get_query($sql)
    {
		$query = $this->db->query($sql);
		if($query->num_rows()) 
		{
		   return $query->result_array();
		}		
		return false;
	}
	public function get_query_count($sql)
    {
		$query = $this->db->query($sql);
		return $query->num_rows();
	}
    
	// function to get get data from table -
    public function get_data($fields = '*', $table, $conditions = NULL, $joins = NULL, $order = NULL, $start = 0, $limit = NULL)
    {
        // build WHERE condition -
		if($conditions != NULL)
		{
            if(is_array($conditions))
			{
                $this->db->where($conditions);
            }
			else
			{
                $this->db->where($conditions, NULL, FALSE);
            }
        }

		// build JOIN condition -
		if($joins != NULL)
		{
			if(is_array($joins))
			{
				foreach($joins as $key => $value)
				{
					if(is_array($value)){
						$this->db->join($key, $value[0],$value[1]);
					}else{
						$this->db->join($key, $value);
					}
				}
			}
			else
			{
				$this->db->join($joins);
			}
		}
		
		// get SELECT fields -
        if($fields != NULL)
		{
            $this->db->select($fields);
        }

		// build ORDER BY condition -
        if($order != NULL)
		{
            $this->db->order_by($order);
        }

		// build LIMIT condition -
        if($limit != NULL)
		{
            $this->db->limit($limit, $start);
        }
		
        $result = $this->db->get($table);
        // add result_array() by deepak
		return $result->result_array();
    }

	// function to get count of records with WHERE and JOIN condition -
    public function get_count($table, $conditions = NULL, $joins = NULL)
    {
        /*$result = $this->get_data('COUNT(*) AS total', $table, $conditions, $joins);

        if($result->num_rows() > 0)
		{
            return $result->row()->total;
        }
		else
		{
            return FALSE;
        }
*/		
        if($conditions != NULL)
		{
            if(is_array($conditions))
			{
                $this->db->where($conditions);
            }
			else
			{
                $this->db->where($conditions, NULL, FALSE);
            }
        }

        // build JOIN condition -
		if($joins != NULL)
		{
			if(is_array($joins))
			{
				foreach($joins as $key => $value)
				{
					$this->db->join($key, $value);
				}
			}
			else
			{
				$this->db->join($joins);
			}
		}

		$num_rows = $this->db->count_all_results($table);

		if($num_rows)
		{
            return $num_rows;
        }
		else
		{
            return FALSE;
        }
    }

	// function to insert data into table -
    public function add_data($table, $data = NULL)
    {
        if ($data == NULL)
		{
            return FALSE;
        }
		
		$this->db->trans_start();
		
		$data['created_on'] 	= date('Y-m-d H:i:s');
		$data['created_by_id'] = $this->session->userdata('user_id');

        $this->db->insert($table, $data);
		
		if ($this->db->trans_status() === FALSE)
		{
			$this->db->trans_rollback();
			return FALSE;
		}
		else
		{
			$this->db->trans_commit();
			return $this->db->insert_id();
			//return TRUE;
		}
    }

	// function to update data in table -
    public function update_data($table, $conditions = NULL, $data = NULL)
    {
        if($data == NULL)
		{
            return FALSE;
        }

        if ($conditions != NULL)
		{
			if(is_array($conditions))
			{
				$this->db->where($conditions);
			}
			else
			{
				$this->db->where($conditions, NULL, FALSE);
			}
		}
		else
		{
			return FALSE;
		}
		
		$data['updated_on'] 	= date('Y-m-d H:i:s');
		$data['updated_by_id']	= $this->session->userdata('user_id');
			
		$this->db->update($table, $data);
		
		if($this->db->trans_status() === FALSE)
		{
			$this->db->trans_rollback();
			return FALSE;
		}
		else
		{
			$this->db->trans_commit();
			//return $this->db->insert_id();
			return TRUE;
		}
    }

	// function to delete record from table -
    public function delete_data($table, $conditions = NULL)
    {
		// NOTE : Here we not actually delete record from database, we just update is_deleted flag from 0 to 1
			
        if($conditions != NULL)
		{
			if(is_array($conditions))
			{
				$this->db->where($conditions);
			}
			else
			{
				$this->db->where($conditions, NULL, FALSE);
			}
		}
		
		$this->db->trans_start();
			
		$data = array(
						'is_deleted'		=> true,
						'deleted_on' 		=> date('Y-m-d H:i:s'),
						'deleted_by_id' 	=> $this->session->userdata('user_id')
				); 
			
		$this->db->update($table, $data);
			
		if($this->db->trans_status() === FALSE)
		{
			$this->db->trans_rollback();
			return FALSE;
		}
		else
		{
			$this->db->trans_commit();
			return TRUE;
		}
    }

    public function delete_record($table, $conditions = NULL)
    {
    	$this->db->trans_start();
    	 if($conditions != NULL)
		{
			if(is_array($conditions))
			{
				$this->db->where($conditions);
			}
			else
			{
				$this->db->where($conditions, NULL, FALSE);
			}
		}

		$this->db->delete($table);

		if($this->db->trans_status() === FALSE)
		{
			$this->db->trans_rollback();
			return FALSE;
		}
		else
		{
			$this->db->trans_commit();
			return TRUE;
		}

    }
	
	// function to get next id of table -
	public function get_next_id($table = NULL)
    {
		if($table == NULL)
		{
            return FALSE;
        }
		
        return (int) $this->db->select('AUTO_INCREMENT')
            ->from('information_schema.TABLES')
            ->where('TABLE_NAME', $table)
            ->where('TABLE_SCHEMA', $this->db->database)->get()->row()->AUTO_INCREMENT;
    }
	
	// function to redirect page with flashdata -
	public function redirect($result = TRUE, $page1, $page2, $action = 'Added')
	{
		if($result === TRUE)
		{
			$this->session->set_flashdata( 'message', array( 'title' => 'Success', 'content' => 'Record '.$action.' Successfully.', 'type' => 's' ));
				
			redirect($page1);
		}
		else
		{
			$this->session->set_flashdata( 'message', array( 'title' => 'Error', 'content' => 'Record Not '.$action.'.', 'type' => 'e' ));
				
			redirect($page2);
		}
	}
	
	// function to convert date from dd-mm-yyyy to yyyy-mm-dd before insert into database -
	public function date_convert($date = NULL, $format = 'd-m-Y')
    {
        if($date != NULL)
		{
			if(strpos($date, '/') !== FALSE)
			{
				$date = str_replace('/', '-', $date);
			}
			
			if(strtolower($format) === 'ymd')
			{
				$format = 'Y-m-d';
			}
			else
			{
				$format = 'd-m-Y';
			}
			return date($format, strtotime($date));
		}
		else
		{
			return FALSE;
		}
    }
	
	// function to convert date format from dd-mm-yyyy to yyyy-mm-dd in form data array -
	public function date_format($data)
    {
		if(is_array($data))
		{
			foreach($data as $key => $value)
			{
				if($value !== '')
				{
					//if(strpos($value, '/') !== FALSE || strpos($value, '-') !== FALSE)	// This line commented and below line added, Date - 06-07-2015
					if(((strpos($value, '/') !== FALSE) || (strpos($value, '-') !== FALSE)) && ((substr_count($value,"/") == 2) || (substr_count($value,"-") == 2)))
					{
						$new_date = $this->date_convert($value, 'ymd');
						
						$data[$key] = $new_date;
					}
				}
			}
			return $data;
		}
		else
		{
			return FALSE;
		}
    }
	
	// function to get field value by id -
	public function get_name_by_id($field, $table, $id = null)
    {
        $data = $this->db->get_where($table, array('pk' => $id));

        if ($data->num_rows() > 0)
		{
            return $data->row()->$field;
        }
		else
		{
            return FALSE;
        }
    }
	
	// function to export data as CSV -
	public function csv_export($query, $file_name = 'export')
    {
		if( ! is_object($query) or ! method_exists($query, 'list_fields'))
        {
			return FALSE;
        }
	
        $this->load->dbutil();
		$this->load->helper('file');
		$this->load->helper('download');
		
		$delimiter = ",";
        $newline = "\r\n";
		
		$data = $this->dbutil->csv_from_result($query, $delimiter, $newline);
		
		force_download($file_name.'.csv', $data);
    }
	
	// function to import data from csv to mysql database -
	public function csv_import($table, $file_path)
    {
		// load csv import library -
		$this->load->library('csvimport');
		 
		if($this->csvimport->get_array($file_path)) 
		{
			$csv_array = $this->csvimport->get_array($file_path);
			
			foreach ($csv_array as $row)
			{
				$insert_data = array(
									'assign_to' 	=> $row['owner'],
									'name' 			=> $row['name'],
									'mobile_no' 	=> $row['mobile_no'],
									'source' 		=> $row['source'],
									'date_added' 	=> date('Y-m-d H:i:s'),
									'added_by_user' => $this->session->userdata('userid')
				);
				
				$this->db->insert($table, $insert_data);
			}
			
			return TRUE;
		}
		else
		{
			return FALSE;
		}
    }
	
	// function to download database backup -
	function db_backup($file_name = 'DB_Backup', $format = 'zip')
   	{
		// Load the DB utility class
		$this->load->dbutil();
			
		// Set Prefrences For Download File.
		$prefs = array(
						'format'      => $format,       // gzip, zip, txt
						'filename'    => $file_name,	// File name - NEEDED ONLY WITH ZIP FILES
						'add_drop'    => TRUE,          // Whether to add DROP TABLE statements to backup file
						'add_insert'  => TRUE,          // Whether to add INSERT data to backup file
						'newline'     => "\n"        	// Newline character used in backup file
				);
		  
		// Backup your entire database and assign it to a variable
		$backup =& $this->dbutil->backup($prefs);
			
		// Load the file helper and write the file to your server
		//$this->load->helper('file');
		//write_file('/path/to/mybackup.gz', $backup); 
			
		// Load the download helper and send the file to your desktop
		$this->load->helper('download');
		
		// download file in zip format
		force_download($file_name.'_'.date("d-m-Y").'.'.$format, $backup);
	}
	
	// function to Restore Database Backup -
	function db_restore($file_name = NULL)
   	{
		if($file_name == NULL)
		{
			return FALSE;		
		}
	
		$sql = file_get_contents($file_name);
			
		foreach (explode(";\n", $sql) as $sql) 
		{
			$sql = trim($sql);
			
			if($sql) 
			{
				if($this->db->query($sql))
				{
					return TRUE;
				}
				else
				{
					return FALSE;
				}
			}
			else
			{
				return FALSE;
			} 
		}
	}
	
	// function to export pdf -
	function pdf_export($html, $file_name, $size, $orientation)
	{
		// create pdf from html contents, this will call below helper function which is defined in dompdf_helper -
		pdf_create($html, $size, $orientation, $file_name);
	}
	
	// function to export excel - Ref - http://dannyherran.com/2011/03/exporting-your-mysql-table-data-with-phpexcel-codeigniter/
	function excel_export($query, $file_name = 'Report', $format = 'Excel5')
	{
		if(!$query)
		{
            return FALSE;
		}
			
		// Starting the PHPExcel library
        $this->load->library('excel');
 
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setTitle("export")->setDescription("none");
 
 		// set active sheet, 0 for sheet 1
        $objPHPExcel->setActiveSheetIndex(0);
 
        // Field names in the first row
        $fields = $query->list_fields();
		
        $col = 0;
        foreach ($fields as $field)
        {
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, 1, $field);
            $col++;
        }
 
        // Fetching the table data
        $row = 2;
        foreach($query->result() as $data)
        {
            $col = 0;
            foreach ($fields as $field)
            {
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $data->$field);
                $col++;
            }
 
            $row++;
        }
 
        $objPHPExcel->setActiveSheetIndex(0);
 
        // Sending headers to force the user to download the file
		if($format == 'Excel5')
		{
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		
        	header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="'.$file_name.'_'.date('d-m-Y').'.xls"');
		}
		else
		{
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'.$file_name.'_'.date('d-m-Y').'.xlsx"');
		}	
		
        header('Cache-Control: max-age=0');
 
        $objWriter->save('php://output');
		exit;
	}
	
	// function to import excel -
	function excel_import($table, $file_name)
	{
		// load the excel library
		$this->load->library('excel');
		
		// get file extensiomn from filename -
		$file_ext = explode('.', $file_name);
		
		if($file_ext[1] == 'xls')
		{
			$objReader = PHPExcel_IOFactory::createReader('Excel5');	// file format between Excel Version 95 to 2003
		}
		else
		{
			$objReader = PHPExcel_IOFactory::createReader('Excel2007'); // file format for Excel 2007
		}
		
		//set to read only
		//$objReader->setReadDataOnly(true);
		
		// read file from path
		$objPHPExcel = $objReader->load($file_name);
		
		// get total no. of sheets in excel -
		//$total_sheets = count($objPHPExcel->getAllSheets());
		
		// set active sheet in excel file -
		$objWorksheet = $objPHPExcel->setActiveSheetIndex(0);	// here we set sheet 1 (i.e. 0) as active sheet
		
		// get total no. of rows in excel sheet -
		$total_rows = $objWorksheet->getHighestRow();
		
		// define array for column names -
		$column_list = array();
		
		// get higest column column from excel -
		$lastColumn = $objWorksheet->getHighestColumn();
		
		// You can convert a column name like 'E' to a column number like 5 using the PHPExcel built-in function -
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($lastColumn);
		$highestColumnIndex--;
		
		$row = 1;
		
		// make an array of column names -
		for($column = 0; $column != $highestColumnIndex; $column++)
		{
			$cell = $objWorksheet->getCellByColumnAndRow($column, $row)->getValue();
			$column_list[] = $cell;
		}
		
		// loop from first data until last data
		for($row = 2; $row <= $total_rows; $row++)
		{
			$data = array();
			
			foreach($column_list as $key => $value)
			{
				$data[$value] = $objWorksheet->getCellByColumnAndRow($key++, $row)->getValue();
			}
		
			// remove empty columns from data array before insert -
			// Ref - http://briancray.com/posts/remove-null-values-php-arrays
			$data = array_filter($data, 'strlen');
			
			// insert data into table -
			$this->db->insert($table, $data);
		}
		
		return TRUE;
	}
	

	
	// function to ulpoad multiple files -
	function upload_file($field_name = NULL, $files = NULL, $config = NULL, $multiple = FALSE)
	{
		// check for empty parameters -
		if($field_name == NULL or $files == NULL or $config == NULL)
		{
            return FALSE;
        }
	
		// define array for uploaded file names -
		$file_name_array = array();
		
		// define array for file upload error -
		$file_error_array = array();
		
		// get count of no. of files in array -
		$count = count($files[$field_name]['tmp_name']);
		
		for($i = 0; $i < $count; $i++)
		{
			// get each file details -
			if($multiple == TRUE)
			{
				$_FILES[$field_name]['name']		= $files[$field_name]['name'][$i];
				$_FILES[$field_name]['type']		= $files[$field_name]['type'][$i];
				$_FILES[$field_name]['tmp_name']	= $files[$field_name]['tmp_name'][$i];
				$_FILES[$field_name]['error']		= $files[$field_name]['error'][$i];
				$_FILES[$field_name]['size']		= $files[$field_name]['size'][$i]; 
			}
			else
			{
				$_FILES = $files;
			}
			
			if($_FILES[$field_name]['error'] === 0)
			{
				// initialize config for file
				
				//added by rahul for get original name with timestamp
				$config['encrypt_name']=FALSE;
				$prefix=$config['prefix']??'';
				// $config['file_name']=$prefix.pathinfo($files[$field_name]['name'][$i], PATHINFO_FILENAME).'_'.time();
				$config['file_name']= $config['file_name'] ? $config['file_name'] :$this->session->userdata('user_id').'_'.time();
				
				$this->upload->initialize($config);
				
				// upload file -
				if($this->upload->do_upload($field_name))
				{
					// get uploaded file data -
					$file_info = $this->upload->data();
					
					// store file name in array -
					$file_name_array[] = $file_info['file_name'];
				}
				else
				{
					// get file upload error -
					$error =  $this->upload->display_errors();
					
					// srore error in array -
					$file_error_array = $error; //Rahul B
					// $file_error_array[$files[$field_name]['name'][$i]] = $error;// bhagwan
				}
			}
		}
		
		// define array for response -
		$response = array();
		
		// file name array and file error array store in response array -
		$response[0] = $file_name_array;
		$response[1] = $file_error_array;
		
		// return response array -
		return $response;
	}
	
	// function to get current logged in user id -
	function get_user_id()
	{
		if(!$this->session->userdata('logged_in'))
		{
			return $this->session->userdata('userid');
		}
		else
		{
			return FALSE;
		}
	}
	
	// function to generate Auto Increment No. with Fix Prefix -
	function get_auto_no($current_no = NULL)
	{
		if($current_no != NULL)
			return ++$current_no;
	}
	
	// status in-active active added by manoj	
	function update_status($where,$active_flag,$tbl_name)
    {    	
    	if (!empty($where)) {
			if($active_flag == 1 ){
				$update_status['is_active'] = true;
			}else{
				$update_status['is_active'] = false;
			}
			$result = $this->update_data($tbl_name,$where,$update_status);
   		
    		if ($result) {
    			$message = array('status'=>true,'message'=>'Status updated Successfully.');
    		}else{
    			$message = array('status'=>false,'message'=>'Status not updated..');
    		}
    		
    	}else{
    		$message = array('status'=>false,'message'=>'Id not found.');
    	}
		echo json_encode($message);
	}
	
	/************************************  datatable ************************************************** */
	/**
	 * get_datatables_master function for use to get data
	 * Create Rahul Badhe
	 * @param        null
	 * @return      mixed|string 
	 */
	function get_datatables_master($sql,$config,$gruop_by='') {
		
		
		$qry=$this->_get_datatables_query_master($sql,$config,$gruop_by);
		if (isset($_POST['length']) && $_POST['length'] != -1) {
			// $this->db->limit($_POST['length'], $_POST['start']);
			$qry.=' LIMIT  '.$_POST['length'].' OFFSET '.$_POST['start'];
		}
		$query = $this->db->query($qry);
		return $query->result();
	}
	
	/**
	 * _get_datatables_query_master function for use search result and order by 
	 * Create Rahul Badhe
	 * @param        null
	 * @return      mixed|string 
	 */
	private function _get_datatables_query_master($sql,$config,$gruop_by='') {
		
		$i = 1;
		
	 	 $cnt = count($config['column_search']);
		foreach ($config['column_search'] as $item) // loop column
		{
			if (isset($_POST['search']) && $_POST['search']['value']) // if datatable send POST for search
			{
				
				$search=strtolower(str_replace("'","",$_POST['search']['value']));
				if ($i === 1) // first loop
				{
					$sql.=" AND (";
				} 
				if ($i == $cnt) // first loop
				{
					$sql.=$item." LIKE '%".$search."%' )";
				}else{
					$sql.=$item." LIKE '%".$search."%' OR ";
				} 
				
			}
			$i++;
		}
 
		if(!empty($gruop_by)){
			$sql.=' GROUP BY '.$gruop_by;
		}

		if (isset($_POST['order']) ) // here order processing
		{
			// $this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
			$sql.=' ORDER BY '.$config['column_order'][$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'];
		} else if (!empty($config['order_by'][key($config['order_by'])])) {
			//$this->db->order_by(key($order), $order[key($order)]);
			$sql.=' ORDER BY '.key($config['order_by']).' '.$config['order_by'][key($config['order_by'])];
		}
		return $sql;
	}
	
	/**
	 * count_all function for use to get data count
	 * Create Rahul Badhe
	 * @param        null
	 * @return      mixed|string 
	 */
	public function count_all_master($tblName) {
		$this->db->from($tblName);
		/* $this->db->where('is_deleted','false');
		$this->db->where('is_active','true'); */
		return $this->db->count_all_results();
	}

	/**
	 * count_filtered function for use to data filter by search post value
	 * Create Rahul Badhe
	 * @param        null
	 * @return      mixed|string 
	 */
	function count_filtered_master($sql,$config,$gruop_by='') {
		$sqlQ=$this-> _get_datatables_query_master($sql,$config,$gruop_by);
		$query = $this->db->query($sqlQ);
		return $query->num_rows();
	}
}
?>