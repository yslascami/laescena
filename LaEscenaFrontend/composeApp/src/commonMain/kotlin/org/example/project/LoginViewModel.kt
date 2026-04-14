package org.example.project

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.launch

class LoginViewModel : ViewModel() {
    private val api = Apiservice()

    private val _loginResult = MutableStateFlow<String>("")
    val loginResult: StateFlow<String> get() = _loginResult

    private val _isLoginSuccess = MutableStateFlow<Boolean>(false)
    val isLoginSuccess: StateFlow<Boolean> get() = _isLoginSuccess

    private val _isRegisterSuccess = MutableStateFlow<Boolean>(false)
    val isRegisterSuccess: StateFlow<Boolean> get() = _isRegisterSuccess

    private val _userRole = MutableStateFlow<String>("")
    val userRole: StateFlow<String> get() = _userRole

    private val _userId = MutableStateFlow<Int?>(null)
    val userId: StateFlow<Int?> get() = _userId

    fun login(email: String, password: String) {
        viewModelScope.launch {
            _loginResult.value = "Cargando..."
            val response = api.loginUsuario(email, password)
            _loginResult.value = response.message

            if (response.success) {
                _userId.value = response.id
                val roleFromApi = response.role?.lowercase() ?: ""
                _userRole.value = when {
                    roleFromApi.contains("admin") -> "superadmin"
                    roleFromApi.contains("artista") -> "artist"
                    roleFromApi.contains("centro") -> "centrocultural"
                    else -> "superadmin"
                }
                _isLoginSuccess.value = true
            } else {
                _isLoginSuccess.value = false
            }
        }
    }

    fun registrar(email: String, password: String, role: String) {
        viewModelScope.launch {
            _loginResult.value = "Registrando..."
            val response = api.registrarUsuario(email, password, role)
            _loginResult.value = response.message
            _isRegisterSuccess.value = response.success
        }
    }
    
    fun resetSuccess() {
        _isLoginSuccess.value = false
        _isRegisterSuccess.value = false
        _userRole.value = ""
    }
}
