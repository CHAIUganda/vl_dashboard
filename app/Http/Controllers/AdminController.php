<?php namespace EID\Http\Controllers;

use EID\Http\Requests;
use EID\Http\Controllers\Controller;

use EID\LiveData;
use EID\User;
use EID\models\Role;
use EID\models\Permission;

class AdminController extends Controller {

	public function create_user(){
		if(\Request::has('username')){
			$data=\Request::all();
			$data['other_facilities'] = serialize($data['other_facilities']);
			
			$user = User::create($data);
			//$role = Role::findOrFail($data['role_id']);
			$user->attachRoles($data['roles']);
			return redirect('admin/create_user')->with('msge',"saving successful");	

		}else{
			$roles = Role::orderby('name')->get();
			$hubs = LiveData::getHubs();
			$facilities = LiveData::getFacilities();

			//$roles = \MyHTML::get_arr_pair($roles, 'display_name');
			$hubs = \MyHTML::get_arr_pair($hubs, 'hub');
			$facilities = \MyHTML::get_arr_pair($facilities, 'facility');

			return view('auth.create_user', compact('hubs', 'facilities', 'roles'));
		}
	}

	public function edit_user($id){
		$user = User::findOrFail($id);
		if(\Request::has('edit')){
			$data=\Request::all();	
			$data['other_facilities'] = serialize($data['other_facilities']);		
			$user->update($data);
			$user->detachRoles();
			$user->attachRoles($data['roles']);

			return redirect('admin/list_users')->with('msge',"saving successful");	

		}else{
			$hubs = LiveData::getHubs();
			$facilities = LiveData::getFacilities();

			$roles = Role::orderby('name')->get();

			$hubs = \MyHTML::get_arr_pair($hubs, 'hub');
			$facilities = \MyHTML::get_arr_pair($facilities, 'facility');

			return view('auth.edit_user', compact('hubs', 'facilities', 'user', 'id', 'roles'));
		}
	}

	public function getIndex(){
		return view('auth.users');
	}

	public function getData(){
		$users = User::orderby('name')->get();

		return \Datatables::of($users)
				->addColumn('roles', function($user){
					$roles_str = "";
					foreach ($user->roles AS $role) $roles_str .= $role->display_name.",";
					return trim($roles_str, ",");
				})
				->addColumn('action', function ($user) {
					$links = [
						'Edit' => "/admin/user_edit/$user->id",
						'Reset Password' => "/admin/user_pass_reset/$user->id"
						];
			        return  \MyHTML::dropdownLinks($links);
			    })
				->make(true);
	}

	public function list_users(){
		$users = User::orderby('name')->get();
		return view('auth.users', compact('users'));

	}

	public function change_password(){
		if(\Request::has('password')){			 

			$data=\Request::all();
			$validator = \Validator::make($data, [
				'current_password'=>'required',
				'password'=>'required',
				'confirm_password'=>'required|same:password']);

			if ($validator->fails()) return redirect()->back()->withErrors($validator)->withInput()->with('msge',trans('general.edit_failure'));
			$user=\Auth::user();
			if(\Hash::check($data['current_password'], $user->password)){
				$user->password=$data['password'];
				$saved=$user->save();
				\Auth::logout();
				return redirect('/')->withFlashMessage("<p class='alert alert-success'>Password successfully changed</p>");
			}else{

				return redirect()->back()->withInput()->with('msge',"<p class='alert alert-danger'>Authentication Failure</p>");
			}

		}else{
			return view("auth.change_password");
		}
		
	}

	public function pass_reset($id){
		$user = User::findOrFail($id);
		$user->password = $user->username."8910";
		$user->save();
		return redirect('admin/list_users')->with('msge',"password reset saving successfully");

	}



		

}