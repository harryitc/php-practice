<?php

require_once 'app/core/Database.php';

class UserModel
{
    private $id;
    private $name;
    private $email;
    private $password;
    private $role;
    private $createdAt;
    private $updatedAt;

    private $db;

    public function __construct($id = null, $name = '', $email = '', $password = '', $role = 'customer')
    {
        $this->db = Database::getInstance();

        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
    }

    /**
     * Find all users
     *
     * @return array
     */
    public function findAll()
    {
        $sql = "SELECT * FROM users ORDER BY name";
        $results = $this->db->query($sql)->fetchAll();

        $users = [];
        foreach ($results as $row) {
            $user = new UserModel(
                $row['id'],
                $row['name'],
                $row['email'],
                $row['password'],
                $row['role']
            );
            $users[] = $user;
        }

        return $users;
    }

    /**
     * Find user by ID
     *
     * @param int $id
     * @return UserModel|null
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM users WHERE id = :id";
        $result = $this->db->query($sql)->fetch(['id' => $id]);

        if (!$result) {
            return null;
        }

        return new UserModel(
            $result['id'],
            $result['name'],
            $result['email'],
            $result['password'],
            $result['role']
        );
    }

    /**
     * Find user by email
     *
     * @param string $email
     * @return UserModel|null
     */
    public function findByEmail($email)
    {
        $sql = "SELECT * FROM users WHERE email = :email";
        $result = $this->db->query($sql)->fetch(['email' => $email]);

        if (!$result) {
            return null;
        }

        return new UserModel(
            $result['id'],
            $result['name'],
            $result['email'],
            $result['password'],
            $result['role']
        );
    }

    /**
     * Save user (insert or update)
     *
     * @return bool
     */
    public function save()
    {
        if ($this->id) {
            return $this->update();
        } else {
            return $this->insert();
        }
    }

    /**
     * Insert new user
     *
     * @return bool
     */
    private function insert()
    {
        // Password is already hashed in setPassword method, so we don't need to hash it again here

        $sql = "INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)";

        $result = $this->db->query($sql)->bind([
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'role' => $this->role
        ])->execute();

        if ($result) {
            $this->id = $this->db->lastInsertId();
            return true;
        }

        return false;
    }

    /**
     * Update existing user
     *
     * @return bool
     */
    private function update()
    {
        // Password is already hashed in setPassword method, so we don't need to hash it again here

        $sql = "UPDATE users SET name = :name, email = :email";
        $params = [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email
        ];

        // Only update password if it's not empty
        if (!empty($this->password)) {
            $sql .= ", password = :password";
            $params['password'] = $this->password;
        }

        $sql .= ", role = :role WHERE id = :id";
        $params['role'] = $this->role;

        return $this->db->query($sql)->bind($params)->execute();
    }

    /**
     * Delete user
     *
     * @return bool
     */
    public function delete()
    {
        if (!$this->id) {
            return false;
        }

        $sql = "DELETE FROM users WHERE id = :id";
        return $this->db->query($sql)->bind(['id' => $this->id])->execute();
    }

    /**
     * Verify password
     *
     * @param string $password
     * @return bool
     */
    public function verifyPassword($password)
    {
        return password_verify($password, $this->password);
    }

    /**
     * Check if password is already hashed
     *
     * @param string $password Password to check, defaults to the current password
     * @return bool
     */
    private function isPasswordHashed($password = null)
    {
        $passwordToCheck = $password ?? $this->password;
        return password_get_info($passwordToCheck)['algo'] !== 0;
    }

    /**
     * Get user ID
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set user ID
     *
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get user name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set user name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get user email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set user email
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Set user password
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        // Always hash the password when it's set
        if (!empty($password)) {
            // Luôn mã hóa mật khẩu mới, không cần kiểm tra nó đã được mã hóa hay chưa
            $this->password = password_hash($password, PASSWORD_DEFAULT);
        }
    }

    /**
     * Get user role
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set user role
     *
     * @param string $role
     */
    public function setRole($role)
    {
        $this->role = $role;
    }

    /**
     * Check if user is admin
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Get created at timestamp
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Get updated at timestamp
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Get username (alias for getName for backward compatibility)
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->name;
    }
}
