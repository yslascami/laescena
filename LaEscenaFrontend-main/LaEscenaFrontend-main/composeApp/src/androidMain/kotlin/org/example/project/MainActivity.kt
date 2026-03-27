package org.example.project

import android.os.Bundle
import android.util.Log
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.unit.dp
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.NavController
import androidx.navigation.compose.*

class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContent {
            MaterialTheme {
                AppNavigation()
            }
        }
    }
}

@Composable
fun AppNavigation() {
    val navController = rememberNavController()

    NavHost(
        navController = navController,
        startDestination = "home"
    ) {
        composable("home") {
            HomeScreen(navController)
        }

        composable("login") {
            LoginScreen(
                onLoginSuccess = { role ->
                    navController.navigate(role) {
                        popUpTo("login") { inclusive = true }
                    }
                }
            )
        }

        composable("register") {
            RegisterScreen(
                onRegisterSuccess = {
                    navController.navigate("login") {
                        popUpTo("register") { inclusive = true }
                    }
                }
            )
        }

        // Perfiles
        composable("superadmin") { SuperAdminScreen() }
        composable("artist") { ArtistScreen() }
        composable("centrocultural") { CentroCulturalScreen() }
    }
}

@Composable
fun HomeScreen(navController: NavController) {
    Column(
        modifier = Modifier
            .fillMaxSize()
            .padding(20.dp),
        verticalArrangement = Arrangement.Center,
        horizontalAlignment = Alignment.CenterHorizontally
    ) {
        Text(
            text = "Bienvenido a La Escena",
            style = MaterialTheme.typography.headlineMedium
        )
        Spacer(modifier = Modifier.height(30.dp))
        Button(onClick = {}) { Text("Ver eventos") }
        Spacer(modifier = Modifier.height(10.dp))
        Button(onClick = {}) { Text("Ver artistas") }
        Spacer(modifier = Modifier.height(10.dp))
        Button(onClick = {}) { Text("Galerías disponibles") }
        Spacer(modifier = Modifier.height(10.dp))
        Button(onClick = {}) { Text("Centro cultural") }
        Spacer(modifier = Modifier.height(10.dp))
        Button(
            onClick = { navController.navigate("register") }
        ) {
            Text("Registrarse (artistas)")
        }
        Spacer(modifier = Modifier.height(20.dp))
        Button(
            onClick = { navController.navigate("login") }
        ) {
            Text("Ingresar")
        }
    }
}

@Composable
fun SuperAdminScreen() {
    Column(
        Modifier.fillMaxSize(),
        verticalArrangement = Arrangement.Center,
        horizontalAlignment = Alignment.CenterHorizontally
    ) {
        Text(text = "Super Admin")
        Spacer(modifier = Modifier.height(10.dp))
        Button(onClick = {}) { Text("Ver eventos") }
        Spacer(modifier = Modifier.height(10.dp))
        Button(onClick = {}) { Text("Ver artistas") }
        Spacer(modifier = Modifier.height(10.dp))
        Button(onClick = {}) { Text("Centro Cultural") }
        Spacer(modifier = Modifier.height(10.dp))
        Button(onClick = {}) { Text("Galerías disponibles") }
        Spacer(modifier = Modifier.height(10.dp))
        Button(onClick = {}) { Text("Crear evento") }
        Spacer(modifier = Modifier.height(10.dp))
        Button(onClick = {}) { Text("Crear artista") }
        Spacer(modifier = Modifier.height(10.dp))
        Button(onClick = {}) { Text("Crear galería") }
    }
}

@Composable
fun ArtistScreen() {
    Column(
        Modifier.fillMaxSize(),
        verticalArrangement = Arrangement.Center,
        horizontalAlignment = Alignment.CenterHorizontally
    ) {
        Text(text = "Artista")
        Spacer(modifier = Modifier.height(10.dp))
        Button(onClick = {}) { Text("Ver eventos") }
        Spacer(modifier = Modifier.height(10.dp))
        Button(onClick = {}) { Text("Perfil") }
        Spacer(modifier = Modifier.height(10.dp))
        Button(onClick = {}) { Text("Mensajes") }
        Spacer(modifier = Modifier.height(10.dp))
        Button(onClick = {}) { Text("Portafolio Artistico") }
    }
}

@Composable
fun CentroCulturalScreen() {
    Column(
        Modifier.fillMaxSize(),
        verticalArrangement = Arrangement.Center,
        horizontalAlignment = Alignment.CenterHorizontally
    ) {
        Text(text = "Centro Cultural")
        Spacer(modifier = Modifier.height(10.dp))
        Button(onClick = {}) { Text("Ver eventos") }
        Spacer(modifier = Modifier.height(10.dp))
        Button(onClick = {}) { Text("Crear evento") }
        Spacer(modifier = Modifier.height(10.dp))
        Button(onClick = {}) { Text("Mensajes") }
        Spacer(modifier = Modifier.height(10.dp))
        Button(onClick = {}) { Text("Ver artistas") }
        Spacer(modifier = Modifier.height(10.dp))
        Button(onClick = {}) { Text("Buscar artistas") }
        Spacer(modifier = Modifier.height(10.dp))
        Button(onClick = {}) { Text("Crear galería") }
        Spacer(modifier = Modifier.height(10.dp))
        Button(onClick = {}) { Text("Ver Galerias") }
    }
}

@Composable
fun RegisterScreen(
    onRegisterSuccess: () -> Unit,
    viewModel: LoginViewModel = viewModel { LoginViewModel() }
) {
    var email by remember { mutableStateOf("") }
    var password by remember { mutableStateOf("") }
    // En el registro de tu servidor Ktor, solo se piden estos campos + role.
    // He quitado el resto para evitar el error de conversion.

    val resultMessage by viewModel.loginResult.collectAsState()
    val isRegisterSuccess by viewModel.isRegisterSuccess.collectAsState()

    LaunchedEffect(isRegisterSuccess) {
        if (isRegisterSuccess) {
            onRegisterSuccess()
            viewModel.resetSuccess()
        }
    }

    Column(
        modifier = Modifier
            .fillMaxSize()
            .padding(20.dp)
            .verticalScroll(rememberScrollState()),
        verticalArrangement = Arrangement.Center,
        horizontalAlignment = Alignment.CenterHorizontally
    ) {
        Text("Registro de Artista", style = MaterialTheme.typography.headlineMedium)

        Spacer(modifier = Modifier.height(20.dp))

        OutlinedTextField(value = email, onValueChange = { email = it }, label = { Text("Correo electrónico") })
        Spacer(modifier = Modifier.height(10.dp))
        OutlinedTextField(
            value = password,
            onValueChange = { password = it },
            label = { Text("Contraseña") },
            visualTransformation = PasswordVisualTransformation()
        )

        Spacer(modifier = Modifier.height(20.dp))

        Button(
            onClick = {
                // Mandamos "artista" como rol fijo para esta pantalla
                viewModel.registrar(email, password, "artista")
            }
        ) {
            Text("Registrarse")
        }

        Spacer(modifier = Modifier.height(10.dp))

        Text(text = resultMessage, color = if (resultMessage.contains("Error")) Color.Red else Color.Black)
    }
}
