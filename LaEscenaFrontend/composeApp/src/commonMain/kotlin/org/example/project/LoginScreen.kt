package org.example.project

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.SpanStyle
import androidx.compose.ui.text.buildAnnotatedString
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.text.withStyle
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.compose.runtime.collectAsState



@Composable
fun LoginScreen(
    onLoginSuccess: (String) -> Unit,
    onNavigateToRegister: () -> Unit = {},
    viewModel: LoginViewModel = viewModel { LoginViewModel() }
) {
    var email by remember { mutableStateOf("") }
    var password by remember { mutableStateOf("") }

    val loginResult by viewModel.loginResult.collectAsState()
    val isLoginSuccess by viewModel.isLoginSuccess.collectAsState()
    val userRole by viewModel.userRole.collectAsState()

    LaunchedEffect(isLoginSuccess) {
        if (isLoginSuccess && userRole.isNotEmpty()) {
            onLoginSuccess(userRole)
            viewModel.resetSuccess()
        }
    }

    Box(
        modifier = Modifier
            .fillMaxSize()
            .background(ColorFondo),
        contentAlignment = Alignment.Center
    ) {
        Card(
            modifier = Modifier
                .fillMaxWidth()
                .padding(24.dp),
            shape = RoundedCornerShape(16.dp),
            colors = CardDefaults.cardColors(containerColor = ColorSuperficie)
        ) {
            Column(
                modifier = Modifier.padding(24.dp),
                verticalArrangement = Arrangement.spacedBy(16.dp)
            ) {
                // Título
                Text(
                    text = "Iniciar Sesión",
                    fontSize = 24.sp,
                    fontWeight = FontWeight.Bold,
                    color = ColorTexto
                )

                // Campo Email
                Column(verticalArrangement = Arrangement.spacedBy(6.dp)) {
                    Text("Email", color = ColorTexto, fontSize = 14.sp)
                    OutlinedTextField(
                        value = email,
                        onValueChange = { email = it },
                        placeholder = { Text("tu@email.com", color = ColorTextoSecundario) },
                        modifier = Modifier.fillMaxWidth(),
                        shape = RoundedCornerShape(8.dp),
                        colors = OutlinedTextFieldDefaults.colors(
                            focusedBorderColor = ColorPrimario,
                            unfocusedBorderColor = Color(0xFF444444),
                            focusedTextColor = ColorTexto,
                            unfocusedTextColor = ColorTexto,
                            cursorColor = ColorPrimario
                        )
                    )
                }

                // Campo Contraseña
                Column(verticalArrangement = Arrangement.spacedBy(6.dp)) {
                    Text("Contraseña", color = ColorTexto, fontSize = 14.sp)
                    OutlinedTextField(
                        value = password,
                        onValueChange = { password = it },
                        placeholder = { Text("••••••••", color = ColorTextoSecundario) },
                        visualTransformation = PasswordVisualTransformation(),
                        modifier = Modifier.fillMaxWidth(),
                        shape = RoundedCornerShape(8.dp),
                        colors = OutlinedTextFieldDefaults.colors(
                            focusedBorderColor = ColorPrimario,
                            unfocusedBorderColor = Color(0xFF444444),
                            focusedTextColor = ColorTexto,
                            unfocusedTextColor = ColorTexto,
                            cursorColor = ColorPrimario
                        )
                    )
                }

                // Botón Entrar
                Button(
                    onClick = { viewModel.login(email, password) },
                    modifier = Modifier.fillMaxWidth().height(50.dp),
                    shape = RoundedCornerShape(8.dp),
                    colors = ButtonDefaults.buttonColors(containerColor = ColorPrimario)
                ) {
                    Text("Entrar", fontSize = 16.sp, fontWeight = FontWeight.Bold, color = Color.White)
                }

                // Mensaje de error/estado
                if (loginResult.isNotEmpty() && loginResult != "Cargando...") {
                    Text(
                        text = loginResult,
                        color = if (loginResult.contains("Error")) Color.Red else ColorTextoSecundario,
                        fontSize = 12.sp,
                        textAlign = TextAlign.Center,
                        modifier = Modifier.fillMaxWidth()
                    )
                }

                // Links
                TextButton(
                    onClick = onNavigateToRegister,
                    modifier = Modifier.fillMaxWidth()
                ) {
                    Text(
                        buildAnnotatedString {
                            withStyle(SpanStyle(color = ColorTextoSecundario)) {
                                append("¿No tienes cuenta de artista? ")
                            }
                            withStyle(SpanStyle(color = ColorPrimario, fontWeight = FontWeight.Bold)) {
                                append("Regístrate")
                            }
                        }
                    )
                }

                TextButton(
                    onClick = {},
                    modifier = Modifier.fillMaxWidth()
                ) {
                    Text(
                        buildAnnotatedString {
                            withStyle(SpanStyle(color = ColorTextoSecundario)) {
                                append("¿Olvidaste tu contraseña? ")
                            }
                            withStyle(SpanStyle(color = ColorPrimario, fontWeight = FontWeight.Bold)) {
                                append("Recuperarla")
                            }
                        }
                    )
                }

                // Usuario de prueba
                Text(
                    text = "Usuario de prueba: artista@laescena.com / demo123",
                    color = ColorTextoSecundario,
                    fontSize = 11.sp,
                    textAlign = TextAlign.Center,
                    modifier = Modifier.fillMaxWidth()
                )
            }
        }
    }
}