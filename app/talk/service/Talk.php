<?php

class Service_Talk {

    public static function getUserByID($id) {
        if (!$id) {
            throw new \Exception('invalid id');
        }

        $where = ['status' => 1, 'id' => $id];
        $user = SDF()->db->get('users', '*', $where);
        if (!$user) {
            throw new \Exception('invalid user');
        }

        return $user;
    }

}
