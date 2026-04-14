package org.example.project

import androidx.compose.foundation.Image
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.*
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.painter.Painter
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import org.jetbrains.compose.resources.painterResource
import laescena.composeapp.generated.resources.Res
import laescena.composeapp.generated.resources.IMG_3474
import laescena.composeapp.generated.resources.IMG_4263
import laescena.composeapp.generated.resources.IMG_6064_edited
import laescena.composeapp.generated.resources.IMG_6194
import laescena.composeapp.generated.resources.IMG_6625_edited
import laescena.composeapp.generated.resources.IMG_6633
import laescena.composeapp.generated.resources.IMG_6790

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun GaleriaScreen() {
    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Galerias disponibles") }
            )
        }) { paddingValues ->
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(paddingValues)
                .padding(horizontal = 38.dp)
                .verticalScroll(rememberScrollState()),
            horizontalAlignment = Alignment.CenterHorizontally
        ) {
            Spacer(modifier = Modifier.height(20.dp))

            Text(
                text = "Souvenir",
                style = MaterialTheme.typography.headlineMedium
            )
            Spacer(modifier = Modifier.height(16.dp))

            GaleriaPic("Recuerdo borroso", painterResource(Res.drawable.IMG_3474))
            Spacer(modifier = Modifier.height(8.dp))
            GaleriaPic("Me entiende más un caballo", painterResource(Res.drawable.IMG_4263))
            Spacer(modifier = Modifier.height(8.dp))
            GaleriaPic("m3m0r14z", painterResource(Res.drawable.IMG_6064_edited))
            Spacer(modifier = Modifier.height(38.dp))
            GaleriaPic("tres viajes más y se acaba", painterResource(Res.drawable.IMG_6194))
            Spacer(modifier = Modifier.height(8.dp))
            GaleriaPic("¿Con todo?", painterResource(Res.drawable.IMG_6625_edited))
            Spacer(modifier = Modifier.height(8.dp))
            GaleriaPic("¿Con chile del que pica?", painterResource(Res.drawable.IMG_6633))
            Spacer(modifier = Modifier.height(8.dp))
            GaleriaPic("50 X persona", painterResource(Res.drawable.IMG_6790))
            Spacer(modifier = Modifier.height(8.dp))
        }
    }
}

@Composable
fun GaleriaPic(titulo: String, imagen: Painter) {
    Column {
        Text(
            text = titulo,
            style = MaterialTheme.typography.headlineSmall,
            fontWeight = FontWeight.Bold
        )
        Spacer(modifier = Modifier.height(8.dp))
        Image(
            painter = imagen,
            contentDescription = titulo,
            modifier = Modifier.fillMaxWidth()
        )
    }
}
