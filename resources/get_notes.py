#!/usr/bin/env python3

import keyring
import sys
import gkeepapi
import json
import argparse

def save_master_token(username, password):
    keep = gkeepapi.Keep()
    success = keep.login(username, password)

    if success:
        master_token = keep.getMasterToken()
        keyring.set_password('google-keep-token', username, master_token)
        print(json.dumps({"code": 0, "message": "Master token created successfully."}))
    else:
        print(json.dumps({"code": -1, "message": "Failed to save master token."}))
        return False

def get_master_token(username):
    master_token = keyring.get_password('google-keep-token', username)
    if master_token:
        return master_token
    else:
        print(json.dumps({"code": -1, "message": "No master token found for the specified user."}))
        return None

def get_notes(username, note_id=None):
    master_token = get_master_token(username)
    if master_token:
        keep = gkeepapi.Keep()
        success = keep.resume(username, master_token)

        if success:
            if note_id:
                gnote = keep.get(note_id)
                if gnote:
                    if isinstance(gnote, gkeepapi.node.List):
                        items = [{"id": item.id, "text": item.text, "checked": item.checked, "sort": item.sort} for item in gnote.items]
                        note_dict = {
                            "id": gnote.id,
                            "type": gnote.type.name,
                            "title": gnote.title,
                            "list": items,
                            "text": gnote.text,
                            "sort": gnote.sort,
                            "color": gnote.color.value,
                            "archived": gnote.archived,
                            "pinned": gnote.pinned,
                            "trashed": gnote.trashed,
                            "created": gnote.timestamps.created.timestamp(),
                            "edited": gnote.timestamps.edited.timestamp(),
                            "updated": gnote.timestamps.updated.timestamp(),
                            "collaborators": gnote.collaborators.all()
                        }
                    else:
                        note_dict = {
                            "id": gnote.id,
                            "type": gnote.type.name,
                            "title": gnote.title,
                            "text": gnote.text,
                            "sort": gnote.sort,
                            "color": gnote.color.value,
                            "archived": gnote.archived,
                            "pinned": gnote.pinned,
                            "trashed": gnote.trashed,
                            "created": gnote.timestamps.created.timestamp(),
                            "edited": gnote.timestamps.edited.timestamp(),
                            "updated": gnote.timestamps.updated.timestamp(),
                            "collaborators": gnote.collaborators.all()
                        }
                    print(json.dumps({"code": 0, "message": note_dict}, sort_keys=True))
                else:
                    print(json.dumps({"code": -1, "message": "No note found with the specified ID."}))
            else:
                gnotes = keep.all()
                note_json = []
                for note in gnotes:
                    note_dict = {}

                    if isinstance(note, gkeepapi.node.List):
                        items = [{"id": item.id, "text": item.text, "checked": item.checked, "sort": item.sort} for item in note.items]
                        note_dict = {
                            "id": note.id,
                            "type": note.type.name,
                            "title": note.title,
                            "list": items,
                            "text": note.text,
                            "sort": note.sort,
                            "color": note.color.value,
                            "archived": note.archived,
                            "pinned": note.pinned,
                            "trashed": note.trashed,
                            "created": note.timestamps.created.timestamp(),
                            "edited": note.timestamps.edited.timestamp(),
                            "updated": note.timestamps.updated.timestamp(),
                            "collaborators": note.collaborators.all()
                        }
                    else:
                        note_dict = {
                            "id": note.id,
                            "type": note.type.name,
                            "title": note.title,
                            "text": note.text,
                            "sort": note.sort,
                            "color": note.color.value,
                            "archived": note.archived,
                            "pinned": note.pinned,
                            "trashed": note.trashed,
                            "created": note.timestamps.created.timestamp(),
                            "edited": note.timestamps.edited.timestamp(),
                            "updated": note.timestamps.updated.timestamp(),
                            "collaborators": note.collaborators.all()
                        }
                    note_json.append(note_dict)
                print(json.dumps({"code": 0, "message": note_json}, sort_keys=True))
            keep.sync()
        else:
            print(json.dumps({"code": -1, "message": "Failed to resume session. Please check credentials and master token."}))

def create_note(username, title, text):
    if not title or not text:
        print("Title and text cannot be empty.")
        return

    master_token = get_master_token(username)
    if master_token:
        keep = gkeepapi.Keep()
        success = keep.resume(username, master_token)

        if success:
            gnote = keep.createNote(title, text)

            # Sync up changes
            keep.sync()
            print(json.dumps({"code": 0, "message": "Note created successfully."}))

        else:
            print(json.dumps({"code": -1, "message": "Failed to resume session. Please check credentials and master token."}))

def search_notes(username, query=None, func=None, labels=None, colors=None, pinned=None, archived=None, trashed=None):
    master_token = get_master_token(username)
    if master_token:
        keep = gkeepapi.Keep()
        success = keep.resume(username, master_token)

        if success:
            gnotes = keep.find(
                query=query,
                func=func,
                labels=labels,
                colors=colors,
                pinned=pinned,
                archived=archived,
                trashed=trashed
            )

            note_json = []
            for note in gnotes:
                note_dict = {}

                if isinstance(note, gkeepapi.node.List):
                    items = [{"id": item.id, "text": item.text, "checked": item.checked} for item in note.checked]
                    note_dict = {
                        "id": note.id,
                        "title": note.title,
                        "list": items,
                        "color": note.color.value,
                        "archived": note.archived,
                        "pinned": note.pinned,
                        "timestamp": note.timestamps.created.timestamp(),
                        "collaborators": note.collaborators.all()
                    }
                else:
                    note_dict = {
                        "id": note.id,
                        "title": note.title,
                        "text": note.text,
                        "color": note.color.value,
                        "archived": note.archived,
                        "pinned": note.pinned,
                        "timestamp": note.timestamps.created.timestamp(),
                        "collaborators": note.collaborators.all()
                    }

                note_json.append(note_dict)

            print(json.dumps({"code": 0, "message": note_json}, sort_keys=True))

            keep.sync()
        else:
            print(json.dumps({"code": -1, "message": "Failed to resume session. Please check credentials and master token"}))

def delete_note(username, note_id):
    master_token = get_master_token(username)
    if master_token:
        keep = gkeepapi.Keep()
        success = keep.resume(username, master_token)

        if success:
            gnote = keep.get(note_id)
            if gnote:
                gnote.delete()
                keep.sync()
                print(json.dumps({"code": 0, "message": "Note has been deleted."}))
            else:
                print(json.dumps({"code": -1, "message": "No note found with the specified ID."}))
        else:
            print(json.dumps({"code": -1, "message": "Failed to resume session. Please check credentials and master token"}))

def restore_note(username, note_id):
    master_token = get_master_token(username)
    if master_token:
        keep = gkeepapi.Keep()
        success = keep.resume(username, master_token)

        if success:
            gnote = keep.get(note_id)
            if gnote:
                gnote.undelete()
                keep.sync()
                print(json.dumps({"code": 0, "message": "Note has been restored."}))
            else:
                print(json.dumps({"code": -1, "message": "No note found with the specified ID."}))
        else:
            print(json.dumps({"code": -1, "message": "Failed to resume session. Please check credentials and master token"}))

def archive_note(username, note_id):
    master_token = get_master_token(username)
    if master_token:
        keep = gkeepapi.Keep()
        success = keep.resume(username, master_token)

        if success:
            gnote = keep.get(note_id)
            if gnote:
                gnote.archive()
                keep.sync()
                print(json.dumps({"code": 0, "message": "Note has been archived."}))
            else:
                print(json.dumps({"code": -1, "message": "No note found with the specified ID."}))
        else:
            print(json.dumps({"code": -1, "message": "Failed to resume session. Please check credentials and master token"}))

def unarchive_note(username, note_id):
    master_token = get_master_token(username)
    if master_token:
        keep = gkeepapi.Keep()
        success = keep.resume(username, master_token)

        if success:
            gnote = keep.get(note_id)
            if gnote:
                gnote.unarchive()
                keep.sync()
                print(json.dumps({"code": 0, "message": "Note has been unarchived."}))
            else:
                print(json.dumps({"code": -1, "message": "No note found with the specified ID."}))
        else:
            print(json.dumps({"code": -1, "message": "Failed to resume session. Please check credentials and master token"}))

def pin_note(username, note_id):
    master_token = get_master_token(username)
    if master_token:
        keep = gkeepapi.Keep()
        success = keep.resume(username, master_token)

        if success:
            gnote = keep.get(note_id)
            if gnote:
                gnote.pinned = True
                keep.sync()
                print(json.dumps({"code": 0, "message": "Note has been pinned."}))
            else:
                print(json.dumps({"code": -1, "message": "No note found with the specified ID."}))
        else:
            print(json.dumps({"code": -1, "message": "Failed to resume session. Please check credentials and master token"}))

def unpin_note(username, note_id):
    master_token = get_master_token(username)
    if master_token:
        keep = gkeepapi.Keep()
        success = keep.resume(username, master_token)

        if success:
            gnote = keep.get(note_id)
            if gnote:
                gnote.pinned = False
                keep.sync()
                print(json.dumps({"code": 0, "message": "Note has been unpinned."}))
            else:
                print(json.dumps({"code": -1, "message": "No note found with the specified ID."}))
        else:
            print(json.dumps({"code": -1, "message": "Failed to resume session. Please check credentials and master token"}))

def modify_note(username, note_id, title=None, text=None, color=None, labels=None, annotations=None, collaborators=None):
    master_token = get_master_token(username)
    if master_token:
        keep = gkeepapi.Keep()
        success = keep.resume(username, master_token)

        if success:
            gnote = keep.get(note_id)
            if gnote:
                if title is not None:
                    gnote.title = title
                if text is not None:
                    gnote.text = text
                if color is not None:
                    gnote.color = color
                if labels is not None:
                    gnote.labels = labels
                if annotations is not None:
                    gnote.annotations = annotations
                if collaborators is not None:
                    gnote.collaborators = collaborators

                keep.sync()
                print(json.dumps({"code": 0, "message": "Note has been modified."}))
            else:
                print(json.dumps({"code": -1, "message": "No note found with the specified ID."}))
        else:
            print(json.dumps({"code": -1, "message": "Failed to resume session. Please check credentials and master token"}))

def add_item(username, note_id, item_text):
    master_token = get_master_token(username)
    if master_token:
        keep = gkeepapi.Keep()
        success = keep.resume(username, master_token)

        if success:
            gnote = keep.get(note_id)
            if gnote:
                if isinstance(gnote, gkeepapi.node.List):
                    new_item = gnote.add(item_text)
                    keep.sync()
                    print(json.dumps({"code": 0, "message": "Item added successfully."}))
                else:
                    print(json.dumps({"code": -1, "message": "The note with the specified ID is not a list."}))
            else:
                print(json.dumps({"code": -1, "message": "No note found with the specified ID."}))
        else:
            print(json.dumps({"code": -1, "message": "Failed to resume session. Please check credentials and master token"}))

def modify_item(username, note_id, item_id, text=None, checked=None, unchecked=None):
    master_token = get_master_token(username)
    if master_token:
        keep = gkeepapi.Keep()
        success = keep.resume(username, master_token)

        if success:
            gnote = keep.get(note_id)
            if gnote:
                if isinstance(gnote, gkeepapi.node.List):
                    item = gnote.get(item_id)
                    if item:
                        if text is not None:
                            item.text = text
                        if checked:
                            item.checked = True
                        elif unchecked:
                            item.checked = False
                        keep.sync()
                        print(json.dumps({"code": 0, "message": "Item has been modified."}))
                    else:
                        print(json.dumps({"code": -1, "message": "No item found with the specified ID."}))
                else:
                    print(json.dumps({"code": -1, "message": "The note with the specified ID is not a list."}))
            else:
                print(json.dumps({"code": -1, "message": "No note found with the specified ID."}))
        else:
            print(json.dumps({"code": -1, "message": "Failed to resume session. Please check credentials and master token"}))

def delete_item(username, note_id, item_id):
    master_token = get_master_token(username)
    if master_token:
        keep = gkeepapi.Keep()
        success = keep.resume(username, master_token)

        if success:
            gnote = keep.get(note_id)
            if gnote:
                if isinstance(gnote, gkeepapi.node.List):
                    item = gnote.get(item_id)
                    if item:
                        item.delete()
                        keep.sync()
                        print(json.dumps({"code": 0, "message": "Item has been deleted."}))
                    else:
                        print(json.dumps({"code": -1, "message": "No item found with the specified ID."}))
                else:
                    print(json.dumps({"code": -1, "message": "The note with the specified ID is not a list."}))
            else:
                print(json.dumps({"code": -1, "message": "No note found with the specified ID."}))
        else:
            print(json.dumps({"code": -1, "message": "Failed to resume session. Please check credentials and master token"}))

def main():
    parser = argparse.ArgumentParser(description="Google Keep CLI for Jeedom")

    parser.add_argument('--username','-u', required=True, help="Google account username")
    parser.add_argument('--command','-c', help="Command to execute")

    # Arguments spécifiques à chaque commande
    subparsers = parser.add_subparsers(title="Commands", dest="command", metavar="COMMAND")

    # Commande "save_master_token"
    parser_save_master_token = subparsers.add_parser("save_master_token", help="Save master token")
    parser_save_master_token.add_argument("password", help="Google account password")

    # Commande "get_notes"
    parser_get_notes = subparsers.add_parser("get_notes", help="Get notes")
    parser_get_notes.add_argument("--note_id", help="ID of the note to restore")

    # Commande "create_note"
    parser_create_note = subparsers.add_parser("create_note", help="Create a new note")
    parser_create_note.add_argument("--title", help="Title of the note")
    parser_create_note.add_argument("--text", help="Text of the note")

    # Commande "search_notes"
    parser_search_notes = subparsers.add_parser("search_notes", help="Search notes")
    parser_search_notes.add_argument('--query','-q', help="Query string for searching notes")

    # Commande "delete_note"
    parser_delete_note = subparsers.add_parser("delete_note", help="Delete a note")
    parser_delete_note.add_argument("--note_id", help="ID of the note to delete")

    # Commande "restore_note"
    parser_restore_note = subparsers.add_parser("restore_note", help="Restore a note")
    parser_restore_note.add_argument("--note_id", help="ID of the note to restore")

    # Commande "archive_note"
    parser_archive_note = subparsers.add_parser("archive_note", help="Archive a note")
    parser_archive_note.add_argument("--note_id", help="ID of the note to archive")

    # Commande "unarchive_note"
    parser_unarchive_note = subparsers.add_parser("unarchive_note", help="Unarchive a note")
    parser_unarchive_note.add_argument("--note_id", help="ID of the note to unarchive")

    # Commande "pin_note"
    parser_pin_note = subparsers.add_parser("pin_note", help="Pin a note")
    parser_pin_note.add_argument("--note_id", help="ID of the note to pin")

    # Commande "unpin_note"
    parser_unpin_note = subparsers.add_parser("unpin_note", help="Unpin a note")
    parser_unpin_note.add_argument("--note_id", help="ID of the note to unpin")

    # Commande "modify_note"
    parser_modify_note = subparsers.add_parser("modify_note", help="Modify a note")
    parser_modify_note.add_argument('--note_id', help='ID of the note to modify.')
    parser_modify_note.add_argument('--title', help='New title for the note.')
    parser_modify_note.add_argument('--text', help='New text content for the note.')
    parser_modify_note.add_argument('--color', help='New color for the note.')
    parser_modify_note.add_argument('--labels', help='New labels for the note.')
    parser_modify_note.add_argument('--annotations', help='New annotations for the note.')
    parser_modify_note.add_argument('--collaborators', help='New collaborators for the note.')

    # Commande "modify_item"
    parser_modify_item = subparsers.add_parser("modify_item", help="Modify an item")
    parser_modify_item.add_argument("--note_id", help="ID of the note to modify")
    parser_modify_item.add_argument("--item_id", help="ID of the item to modify")
    parser_modify_item.add_argument("--text", "-t", help="New text for the item")
    parser_modify_item.add_argument("--checked", action="store_true", help="Set checked status for the item")
    parser_modify_item.add_argument("--unchecked", action="store_true", help="Set unchecked status for the item")

    # Commande "add_item"
    parser_add_item = subparsers.add_parser("add_item", help="Add an item")
    parser_add_item.add_argument("--note_id", help="ID of the note to add the item to")
    parser_add_item.add_argument("--text", help="Text of the item to add")

    # Commande "delete_item"
    parser_delete_item = subparsers.add_parser("delete_item", help="Delete an item")
    parser_delete_item.add_argument("--note_id", help="ID of the note to delete the item to")
    parser_delete_item.add_argument("--item_id", help="ID of the item to delete")

    args = parser.parse_args()
    # Vérifier si l'argument --username est fourni
    if not args.username:
        print("Erreur : l'argument --username est requis.")
        return

    # Exécuter la commande appropriée en fonction des arguments fournis
    if args.command == "save_master_token":
        save_master_token(args.username, args.password)
    elif args.command == "get_notes":
        if hasattr(args, 'note_id') and args.note_id:
            get_notes(args.username, args.note_id)
        else:
            get_notes(args.username)
    elif args.command == "create_note":
        create_note(args.username, args.title, args.text)
    elif args.command == "search_notes":
        search_notes(args.username, args.query)
    elif args.command == "delete_note":
        if not args.note_id:
            print("Erreur : l'identifiant de la note est requis.")
            return
        delete_note(args.username, args.note_id)
    elif args.command == "restore_note":
        if not args.note_id:
            print("Erreur : l'identifiant de la note est requis.")
            return        restore_note(args.username, args.note_id)
    elif args.command == "archive_note":
        if not args.note_id:
            print("Erreur : l'identifiant de la note est requis.")
            return
        archive_note(args.username, args.note_id)
    elif args.command == "unarchive_note":
        if not args.note_id:
            print("Erreur : l'identifiant de la note est requis.")
            return
        unarchive_note(args.username, args.note_id)
    elif args.command == "pin_note":
        if not args.note_id:
            print("Erreur : l'identifiant de la note est requis.")
            return
        pin_note(args.username, args.note_id)
    elif args.command == "unpin_note":
        if not args.note_id:
            print("Erreur : l'identifiant de la note est requis.")
            return
        unpin_note(args.username, args.note_id)
    elif args.command == "modify_note":
        if not args.note_id:
            print("Erreur : l'identifiant de la note est requis.")
            return
        modify_note(args.username, args.note_id, args.title, args.text, args.color, args.labels, args.annotations, args.collaborators)
    elif args.command == "modify_item":
        if not args.note_id:
            print("Erreur : l'identifiant de la note est requis.")
            return
        if not args.item_id:
            print("Erreur : l'identifiant de l'item est requis.")
            return
        if args.checked:
            modify_item(args.username, args.note_id, args.item_id, text=args.text, checked=True)
        elif args.unchecked:
            modify_item(args.username, args.note_id, args.item_id, text=args.text, unchecked=True)
        else:
            modify_item(args.username, args.note_id, args.item_id, text=args.text)
    elif args.command == "add_item":
        if not args.note_id:
            print("Erreur : l'identifiant de la note est requis.")
            return
        add_item(args.username, args.note_id, args.text)
    elif args.command == "delete_item":
        if not args.note_id:
            print("Erreur : l'identifiant de la note est requis.")
            return
        if not args.item_id:
            print("Erreur : l'identifiant de l'item est requis.")
            return
        delete_item(args.username, args.note_id, args.item_id)
    else:
        print("Invalid command")

if __name__ == "__main__":
    main()