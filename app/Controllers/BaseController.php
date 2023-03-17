<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\FileUpload;
use App\Models\User;
use App\Models\UserGroup;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Config\Services;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
class BaseController extends Controller
{
	/**
	 * Instance of the main Request object.
	 *
	 * @var CLIRequest|IncomingRequest
	 */
	protected $request;

	/**
	 * An array of helpers to be loaded automatically upon
	 * class instantiation. These helpers will be available
	 * to all other controllers that extend BaseController.
	 *
	 * @var array
	 */
	protected $helpers = [];

	/**
	 * Data to send to view.
	 * @var array;
	 */
	protected $data = [];

	/**
	 * Constructor.
	 */
	public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
	{
		// Do Not Edit This Line
		parent::initController($request, $response, $logger);

		// Preload any models, libraries, etc, here.

		// E.g.: $this->session = \Config\Services::session();

		/**
		 * Reset last error. This is global error message if error occurred.
		 * Use getLastError() to get last error message.
		 */

		if (!isCLI()) {
			setLastError();
		}

		// Force to HTTPS connection.
		if (!isSecure() && !isCLI()) {
			redirect()->to('https://' . $_SERVER['HTTP_HOST']);
		}

		/**
		 * Resource Versioning
		 */
		$this->data['resver'] = (isEnv('development') ? bin2hex(random_bytes(4)) : '1.0.0');

		//--------------------------------------------------------------------
		// Preload any models, libraries, etc, here.
		//--------------------------------------------------------------------
		// E.g.: $this->session = \Config\Services::session();

		$this->data['page'] = []; // data['page'] is reserved.

		if (isLoggedIn()) {
			// Set language locale for global lang().
			Services::language(session('login')->lang);

			// Refresh groups and permissions.
			$login = session('login');

			$user = User::getRow(['id' => $login->user_id]);
			$login->groups = explode(',', $user->groups);
			$login->permissions = [];

			foreach ($login->groups as $group) {
				$userGroup = UserGroup::getRow(['name' => $group]);

				if ($userGroup) {
					$login->permissions = array_merge($login->permissions, getJSON($userGroup->permissions, true));
				}
			}

			session()->set('login', $login);
			// End refresh groups and permissions.

			$lang = [
				'App' 		=> include(APPPATH . 'Language/' . session('login')->lang . '/App.php'),
				'Msg' 		=> include(APPPATH . 'Language/' . session('login')->lang . '/Msg.php'),
				'Status' 	=> include(APPPATH . 'Language/' . session('login')->lang . '/Status.php')
			];

			// lang64 used by javascript only.
			$this->data['lang64'] = base64_encode(json_encode($lang));
			unset($lang);

			$this->data['permission64'] = base64_encode(json_encode(session('login')->permissions));
		}
	}

	/**
	 * Build new page.
	 * @param array $data [ page['content'], page['url']
	 */
	protected function buildPage($data = [])
	{
		if (isAJAX()) {
			// Load contents.
			$currentUrl = current_url() . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '');
			$data['page']['content'] = view($data['page']['content'], $data);
			$data['page']['url'] = $currentUrl;

			$this->response(200, $data['page']);
		} else if (requestMethod() == 'GET') {
			if (isLoggedIn()) {
				echo view('content', $data);
			}
		}
	}

	/**
	 * Send API response to client.
	 * @param int $code Code to response.
	 * @param array $data Respons data.
	 */
	protected static function response(int $code, $data = [])
	{
		if (!is_array($data)) throw new \Exception('Response 2nd parameter is not an array.');

		$data = array_merge(['code' => intval($code)], $data);

		if (!isCLI()) {
			http_response_code($code);
		}

		sendJSON($data);
	}

	/**
	 * Use attachment.
	 * @param array $data Data.
	 * @param string $attachment Attachment to replace (optional).
	 */
	protected function useAttachment(array $data, string $attachment = NULL)
	{
		$upload = new FileUpload();

		if ($upload->has('attachment')) {
			if ($upload->getSize('mb') > 2) {
				$this->response(400, ['message' => lang('Msg.attachmentExceed')]);
			}

			$data['attachment'] = $upload->store(NULL, $attachment);
		}

		return $data;
	}
}
