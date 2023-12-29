<?php

use Cart\Customer;
use Cart\Cart;

class ULCart
{
    protected DB $db;
    protected Session $session;
    protected Customer $customer;
    protected Cart $cart;

    public function clearOldCartBackups(): void
    {
        $result = $this->db->query('SELECT ' . DB_PREFIX . 'ul_cart_backup.session_id AS id, ' . DB_PREFIX . 'session.session_id 
        FROM oc_ul_cart_backup LEFT JOIN ' . DB_PREFIX . 'session 
        ON ' . DB_PREFIX . 'session.session_id=' . DB_PREFIX . 'ul_cart_backup.session_id 
        HAVING ' . DB_PREFIX . 'session.session_id IS NULL LIMIT 10');
        if (!empty($result->rows)) {
            $orphans = array_column($result->rows, 'id');
            $this->db->query(
                sprintf(
                    'DELETE FROM ' . DB_PREFIX . 'ul_cart_backup WHERE session_id IN (%s)',
                    '"' . implode('", "', $orphans) . '"'
                )
            );
        }
    }

    public function clearCurrentBackup(): void
    {
        $this->db->query(
            sprintf(
                'DELETE FROM ' . DB_PREFIX . 'ul_cart_backup WHERE 
            session_id="%s" and api_id=%s and customer_id=%s',
                $this->db->escape($this->session->getId()),
                (int)($this->session->data['api_id'] ?? 0),
                (int)$this->customer->getId(),
            )
        );
    }

    /**
     * @throws JsonException
     */
    public function clearOrderedProducts(): void
    {
        $products = [];
        foreach ($this->cart->getProducts() as $product) {
            $products[] = [
                'product_id' => $product['product_id'],
                'quantity' => $product['quantity'],
                'option' => $product['option'],
                'recurring' => $product['recurring']
            ];
        }
        $products = json_encode($products, JSON_THROW_ON_ERROR);
        $this->db->query(
            sprintf(
                'INSERT INTO ' . DB_PREFIX . 'ul_cart_backup SET 
            session_id="%s", api_id=%s, customer_id=%s, products="%s"
            ON DUPLICATE KEY UPDATE products="%s"
            ',
                $this->db->escape($this->session->getId()),
                (int)($this->session->data['api_id'] ?? 0),
                (int)$this->customer->getId(),
                $this->db->escape($products),
                $this->db->escape($products),
            )
        );
        $this->cart->clear();
    }

    /**
     * @throws JsonException
     */
    public function restoreOrderedProducts(): void
    {
        if ($this->cart->hasProducts()) {
            return;
        }
        $result = $this->db->query(
            sprintf(
                'SELECT products FROM ' . DB_PREFIX . 'ul_cart_backup WHERE 
            session_id="%s" and api_id=%s and customer_id=%s',
                $this->db->escape($this->session->getId()),
                (int)($this->session->data['api_id'] ?? 0),
                (int)$this->customer->getId(),
            )
        );

        $this->clearCurrentBackup();

        $products = (!empty($result->row['products'])) ?
            json_decode($result->row['products'], true, 512, JSON_THROW_ON_ERROR) : [];

        foreach ($products as $product) {
            $this->cart->add(
                $product['product_id'],
                $product['quantity'],
                $product['option'],
                (int)$product['recurring']
            );
        }
    }

    /**
     * @param DB $db
     *
     * @return self
     */
    public function setDb(DB $db): self
    {
        $this->db = $db;
        return $this;
    }

    /**
     * @param Session $session
     *
     * @return self
     */
    public function setSession(Session $session): self
    {
        $this->session = $session;
        return $this;
    }

    /**
     * @param Customer $customer
     *
     * @return self
     */
    public function setCustomer(Customer $customer): self
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * @param Cart $cart
     *
     * @return self
     */
    public function setCart(Cart $cart): self
    {
        $this->cart = $cart;
        return $this;
    }
}
