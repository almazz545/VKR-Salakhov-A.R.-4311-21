const AUTH_URL = "http://localhost/vue-tipping-project/backend/auth";
const API_URL = "http://localhost/vue-tipping-project/backend/api";

export const auth = {
  async register(email, password) {
    const response = await fetch(`${AUTH_URL}/register.php`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ email, password }),
    });

    const data = await response.json();
    if (!response.ok) {
      throw new Error(data.error || "Ошибка регистрации");
    }

    if (data.token) {
      localStorage.setItem("token", data.token);
      localStorage.setItem("user", JSON.stringify(data.user));
    }

    return data;
  },

  async login(email, password) {
    const response = await fetch(`${AUTH_URL}/login.php`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ email, password }),
    });

    const data = await response.json();
    if (!response.ok) {
      throw new Error(data.error || "Ошибка входа");
    }

    if (data.token) {
      localStorage.setItem("token", data.token);
      localStorage.setItem("user", JSON.stringify(data.user));
    }

    return data;
  },

  async logout() {
    const token = localStorage.getItem("token");
    const response = await fetch(`${AUTH_URL}/logout.php`, {
      method: "POST",
      headers: {
        Authorization: token,
      },
    });

    const data = await response.json();
    if (!response.ok) {
      throw new Error(data.error || "Ошибка выхода");
    }

    localStorage.removeItem("token");
    localStorage.removeItem("user");

    return data;
  },

  async getCollection() {
    const token = localStorage.getItem("token");
    const response = await fetch(`${API_URL}/get_collection.php`, {
      method: "GET",
      headers: {
        Authorization: token,
      },
    });

    const data = await response.json();
    if (!response.ok) {
      throw new Error(data.error || "Ошибка загрузки коллекции");
    }

    return data.collection;
  },

  async getCollectionById(id) {
    const token = localStorage.getItem("token");
    const response = await fetch(
      `${API_URL}/get_collection_by_id.php?id=${id}`,
      {
        method: "GET",
        headers: {
          Authorization: token,
        },
      }
    );

    const data = await response.json();
    if (!response.ok) {
      throw new Error(data.error || "Коллекция не найдена");
    }

    return data.collection;
  },

  async createCollection(title, description, goal) {
    const token = localStorage.getItem("token");
    const response = await fetch(`${API_URL}/create_collection.php`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Authorization: token,
      },
      body: JSON.stringify({ title, description, goal }),
    });

    const data = await response.json();
    if (!response.ok) {
      throw new Error(data.error || "Ошибка создания коллекции");
    }

    return data.collection;
  },

  async updateCollection(id, title, description, goal, collected) {
    const response = await fetch(`${API_URL}/update_collection.php`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ id, title, description, goal, collected }),
    });

    const data = await response.json();
    if (!response.ok) {
      throw new Error(data.error || "Ошибка обновления сбора");
    }

    return data.collection;
  },

  async deleteCollection(id) {
    const token = localStorage.getItem("token");
    const response = await fetch(`${API_URL}/delete_collection.php`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Authorization: token,
      },
      body: JSON.stringify({ id }),
    });

    const data = await response.json();
    if (!response.ok) {
      throw new Error(data.error || "Ошибка удаления сбора");
    }

    return data;
  },
};
