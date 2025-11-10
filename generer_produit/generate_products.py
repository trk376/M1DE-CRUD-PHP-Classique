#!/usr/bin/env python3
"""
Script pour générer des produits avec Faker
Génère n produits avec des données aléatoires et les exporte en SQL
"""

import sys
import random
from datetime import datetime, timedelta
from faker import Faker

# Initialiser Faker avec la locale française
fake = Faker('fr_FR')

# Catégories de produits
CATEGORIES = [
    "Électronique",
    "Alimentation",
    "Papeterie",
    "Meubles",
    "Vêtements",
    "Sport",
    "Jouets",
    "Bricolage",
    "Maison",
    "Jardin"
]

def generate_products(n=10, start_id=1):
    """
    Génère n produits avec des données aléatoires
    
    Args:
        n (int): Nombre de produits à générer
        start_id (int): ID de départ pour les produits
    
    Returns:
        list: Liste des produits générés
    """
    products = []
    
    for i in range(n):
        product_id = start_id + i
        
        category = random.choice(CATEGORIES)
        
        designation = fake.word().capitalize() + " " + fake.word()
        
        price = round(random.uniform(1, 500), 2)
        
        date_in = (datetime.now() - timedelta(days=random.randint(0, 30))).date()
        
        stock = random.randint(0, 200)
        
        promo = None
        if random.random() < 0.3:
            promo = round(random.uniform(5, 50), 2)
        
        products.append({
            'id': product_id,
            'category': category,
            'designation': designation,
            'price': price,
            'date': date_in,
            'stock': stock,
            'promo': promo
        })
    
    return products

def export_to_sql(products, output_file='products.sql'):
    """
    Exporte les produits en SQL INSERT statements
    
    Args:
        products (list): Liste des produits
        output_file (str): Nom du fichier de sortie
    """
    with open(output_file, 'w', encoding='utf-8') as f:
        f.write("-- Script SQL pour insérer les produits générés\n")
        f.write("-- Généré avec Faker\n\n")
        f.write("INSERT INTO `produit` (`id_p`, `type_p`, `designation_p`, `prix_ht`, `date_in`, `stock_p`, `image_p`, `ppromo`) VALUES\n")
        
        for i, product in enumerate(products):
            promo_val = f"'{product['promo']}'" if product['promo'] else 'NULL'
            
            line = f"({product['id']}, '{product['category']}', '{product['designation']}', {product['price']}, '{product['date']}', {product['stock']}, 'default.png', {promo_val})"
            
            if i < len(products) - 1:
                line += ","
            else:
                line += ";"
            
            f.write(line + "\n")
    
    print(f"✓ {len(products)} produits exportés vers {output_file}")

def main():
    """Fonction principale"""
    
    # Récupérer le nombre de produits à générer
    if len(sys.argv) > 1:
        try:
            n = int(sys.argv[1])
        except ValueError:
            print("Usage: python generate_products.py [nombre_de_produits]")
            sys.exit(1)
    else:
        n = 10
    
    # Générer les produits
    products = generate_products(n=n)
    
    # Exporter en SQL
    export_to_sql(products, 'products.sql')
    
    print(f"\n✓ Génération terminée!")

if __name__ == "__main__":
    main()
