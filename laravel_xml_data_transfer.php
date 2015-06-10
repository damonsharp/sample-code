<?php

    /**
     * Automate the upload of an xml file using Laravel Artisan in tandem with CRON
     *
     * Check the last_xml_dump value from the options table against the update_at field
     * from the registrations table to determine the data to query (only grabbing
     * incremental/changed entries) on a timed basis (CRON via Artisan command).
     *
     * Once the data is queried, build the XML file for upload and a manifest file per the
     * specifications provided by the client, upload to the remote server, and update the
     * 'last_xml_dump' value in the options table for next time.
     * 
     */
	class XMLDataTransfer {

		/**
		 * Store a new DateTime() object
		 * 
		 * @var object
		 */
		private $_new_date_time;


		/**
		 * Store a new DateTime object in 'Y-m-d H:i:s' format
		 * 
		 * @var string
		 */
		private $_now;


		/**
		 * Store the last XML dump from the options database table
		 * 
		 * @var string
		 */
		private $_last_xml_dump;


		/**
		 * Store the file directory for writing XML and manifest files to
		 * 
		 * @var string
		 */
		private $_file_directory;


		/**
		 * Store a filename prefix based on a formatted date ('Ymd')
		 * 
		 * @var string
		 */
		private $_filename_prefix;


		/**
		 * The remote FTP directory to store the file in
		 * 
		 * @var string
		 */
		private $_remote_ftp_directory;


		/**
		 * Set some class properties and kickoff the _processDataTransfer method
		 * 
		 */
	    public function __construct()
	    {
	    	// NOTE: Order matters. DO NOT rearrage the following class properties...
	    	$this->_new_date_time = new DateTime();
	    	$this->_now = $this->_getNow('Y-m-d H:i:s');
	        $this->_last_xml_dump = $this->_getLastXmlDump();
	        $this->_file_directory = $this->_getFileDirectoryPath();
	        $this->_filename_prefix = $this->_getFilenamePrefix('Ymd');
	        $this->_remote_ftp_directory = $_ENV['FTP_PATH'];

	        $this->_processDataTransfer();
	    }


	    /**
	     * Get this party started. This function is called on a 24 basis via a CRON job
	     * 
	     * @return void
	     */
	    private function _processDataTransfer()
	    {
            // Query the registrations database table for any registrations that were updated
            // and/or added AFTER the last_xml_dump date. If the last_xml_dump value is empty, then
            // just get all registrations from that table
            if ( empty( $this->_last_xml_dump ) || '0000-00-00 00:00:00' == $this->_last_xml_dump )
            {
                $registration_data = Registration::all();
            }
            else
            {
                $registration_data = Registration::withTrashed()->where('updated_at', '>', $this->_last_xml_dump)->get();
            }
            
            /*** Create XML File for saving and upload ***/
            // Get the file contents by making a view from the data and rendering it.
            $xml_file_contents = $this->_getFileContents('xml.xml_view', $registration_data, true);

            // Create the file name using a date prefix and suffix
            $xml_filename = $this->_filename_prefix . '_ICS.XML';

            // Create the full file path for writing the XML file
            $xml_file = $this->_file_directory . $xml_filename;

            // Write the XML file to the server
            $this->_writeFileContents($xml_file, $xml_file_contents);

            // Check the the file was written correctly and if so, upload to server
            if ( file_exists($xml_file) )
            {
	            $this->_uploadFileToRemoteServer($xml_file, $this->_remote_ftp_directory . $xml_filename);
            }

            /*** Create manifest text file that describes XML file above ***/
            // Create the data for the manifest file
            $manifest_data = array(
                'xml_filename' => $xml_filename,
                'file_line_count' => $this->_getLineCount($xml_file)
            );

            // Get the file contents by making a view from the data and rendering it.
            $xml_manifest_file_contents = $this->_getFileContents('xml.manifest_view', $manifest_data);

            // Create the file name using a date prefix and suffix
            $xml_manifest_filename = $this->_filename_prefix . '_ICS_MANIFEST.TXT';

            // Create the full file path for writing the TXT manifest file
            $xml_manifest_file = $this->_file_directory . $xml_manifest_filename;

            // Write the manifest file to the server
            $this->_writeFileContents($xml_manifest_file, $xml_manifest_file_contents);

            // Check the the file was written correctly and if so, upload to server
            if ( file_exists($xml_manifest_file) )
            {
            	$this->_uploadFileToRemoteServer($xml_manifest_file, $this->_remote_ftp_directory . $xml_manifest_filename);
            }

            // Update the last_xml_dump option in the database with the current date ($now set above).
            // This will allow for querying incremental data for upload.
            Option::where('option_name', 'last_xml_dump')->update(array('option_value' => $this->_now));
	    }


	    /**
	     * Get the 'last_xml_dump' value from the options table for comparison
	     * 
	     * @return string last XML database dump
	     */
	    private function _getLastXmlDump()
	    {
	    	return Option::where('option_name','last_xml_dump')->pluck('option_value');
	    }


	    /**
	     * Get the new DateTime object formatted as datetime
	     *
	     * @param  string $date_format The date format to return
	     * @return string date as datetime
	     */
	    private function _getNow($date_format)
	    {
	        return $this->_new_date_time->format($date_format);
	    }


	    /**
	     * Get the file directory path for writing XML and TXT manifest
	     * files to (storing in app/storage/xml)
	     * 
	     * @return string storage file directory path
	     */
	    private function _getFileDirectoryPath()
	    {
	    	return storage_path() . '/xml/';
	    }


	    /**
	     * Get the filename prefix based on the new DateTime object
	     *
	     * @param  string $date_format The date format to return
	     * @return string formatted date
	     */
	    private function _getFilenamePrefix($date_format)
	    {
	    	return $this->_new_date_time->format( $date_format );
	    }


	    /**
	     * Get the line count for the XML file written to the directory above
	     * 
	     * @param  string $file file, including directory path
	     * @return integer XML file line count
	     */
	    private function _getLineCount( $file )
	    {
	        $fhandle = fopen($file, 'rb');
	        // NOTE: We're using trim() to trim the last line break and spacing off the end
	        // of the xml file created above, so this needs to = 1 to put that line back on.
	        $line_count = 1;
	        while ( !feof($fhandle) )
	        {
	            $line_count += substr_count(fread($fhandle, 8192), "\n");
	        }
	        fclose($fhandle);
	        return $line_count;
	    }


	    /**
	     * Get the file contents based on a rendered view. The option 3rd param $count
	     * controls whether the data passed to the view contains the record count from the
	     * database.  This is needed for the XML file, but not the manifest text file. The rendered
	     * view is then writted to the storage directory above before being sent to the
	     * remote server for storage.
	     * 
	     * @param  string  $view  The laravel view file to render
	     * @param  array  $data  Data passed into the view before rendering
	     * @param  boolean $count Whether or not to include the database recored count in the data
	     *         passed to the view
	     * @return string the trimmed, rendered view with embedded data.
	     */
	    private function _getFileContents($view, $data, $count = false)
	    {
	    	if ( $count )
	    	{
	    		$record_count = is_array($data) ? 0 : $data->count();
	    		return trim(View::make($view, array('data' => $data, 'count' => $record_count))->render());
	    	}
	    	return trim(View::make($view, array('data' => $data))->render());
	    }


	    /**
	     * Write the file contents of the XML file or text file rendered above
	     * to the storage directory
	     * 
	     * @param  string $file the name of the file to be written
	     * @param  string $contents the file's contents to be written
	     * @return boolean success or failure
	     */
	    private function _writeFileContents($file, $contents)
	    {
	    	return file_put_contents($file, $contents);
	    }


	    /**
	     * Upload the files written above to the remote server via FTP
	     * 
	     * @param  string $file the name of the file to be uploaded, including file path
	     * @param  string $remote_path the path to upload the file to on the remote server
	     * @return boolean success or failure
	     */
	    private function _uploadFileToRemoteServer($file, $remote_path)
	    {
	    	return SSH::put($file, $remote_path);
	    }

	}