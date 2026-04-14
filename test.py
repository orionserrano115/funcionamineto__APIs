import json
import requests
from requests.exceptions import RequestException

API_URL = "http://localhost/api_usuarios/api_usuarios.php"


def mostrar_respuesta(response):
    print(f"\nHTTP {response.status_code}")
    try:
        data = response.json()
        print(json.dumps(data, indent=4, ensure_ascii=False))
    except ValueError:
        texto = response.text.strip()
        print(texto if texto else "(Sin contenido)")


def leer_entero(mensaje):
    while True:
        valor = input(mensaje).strip()
        if valor.isdigit():
            return int(valor)
        print("Ingresa un numero valido.")


def leer_datos_usuario():
    nombre = input("Nombre: ").strip()
    correo = input("Correo: ").strip()
    telefono = input("Telefono: ").strip()
    return {"nombre": nombre, "correo": correo, "telefono": telefono}


def obtener_todos():
    response = requests.get(API_URL, timeout=10)
    mostrar_respuesta(response)


def obtener_por_id():
    id_usuario = leer_entero("ID del usuario: ")
    response = requests.get(API_URL, params={"id": id_usuario}, timeout=10)
    mostrar_respuesta(response)


def crear_usuario():
    datos = leer_datos_usuario()
    response = requests.post(API_URL, json=datos, timeout=10)
    mostrar_respuesta(response)


def actualizar_usuario():
    id_usuario = leer_entero("ID del usuario a actualizar: ")
    datos = leer_datos_usuario()
    response = requests.put(API_URL, params={"id": id_usuario}, json=datos, timeout=10)
    mostrar_respuesta(response)


def eliminar_usuario():
    id_usuario = leer_entero("ID del usuario a eliminar: ")
    response = requests.delete(API_URL, params={"id": id_usuario}, timeout=10)
    mostrar_respuesta(response)


def mostrar_menu():
    print("\n=== MENU API USUARIOS ===")
    print("1. Listar usuarios")
    print("2. Obtener usuario por ID")
    print("3. Crear usuario")
    print("4. Actualizar usuario")
    print("5. Eliminar usuario")
    print("0. Salir")


def main():
    while True:
        mostrar_menu()
        opcion = input("Elige una opcion: ").strip()

        try:
            if opcion == "1":
                obtener_todos()
            elif opcion == "2":
                obtener_por_id()
            elif opcion == "3":
                crear_usuario()
            elif opcion == "4":
                actualizar_usuario()
            elif opcion == "5":
                eliminar_usuario()
            elif opcion == "0":
                print("Saliendo...")
                break
            else:
                print("Opcion no valida.")
        except RequestException as error:
            print(f"Error de conexion con la API: {error}")


main()
