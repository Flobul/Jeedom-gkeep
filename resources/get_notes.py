#!/usr/bin/env python3

import keyring
import gkeepapi
import json
import argparse

class GoogleKeepManager:
    CODE_SUCCESS = 0
    CODE_ERROR = -1

    MSG_MASTER_TOKEN_CREATED = "Master token created successfully."
    MSG_FAILED_SAVE_MASTER_TOKEN = "Failed to save master token."
    MSG_NO_NOTE_FOUND = "No note found with the specified ID."
    MSG_FAILED_RESUME_SESSION = "Failed to resume session. Please check credentials and master token."
    MSG_NOTE_CREATED = "Note created successfully."
    MSG_LIST_CREATED = "List created successfully."
    MSG_NOTE_DELETED = "Note has been deleted."
    MSG_NOTE_RESTORED = "Note has been restored."
    MSG_NOTE_ARCHIVED = "Note has been archived."
    MSG_NOTE_UNARCHIVED = "Note has been unarchived."
    MSG_NOTE_PINNED = "Note has been pinned."
    MSG_NOTE_UNPINNED = "Note has been unpinned."
    MSG_NOTE_MODIFIED = "Note has been modified."
    MSG_NOTE_NOT_IN_LIST = "The note with the specified ID is not a list."
    MSG_ITEM_ADDED = "Item added successfully."
    MSG_ITEM_MODIIED = "Item has been modified."
    MSG_ITEM_DELETED = "Item has been deleted."
    MSG_NO_ITEM_FOUND = "No item found with the specified ID."
    MSG_NO_LABEL_FOUND = "No label found."
    MSG_LABEL_CREATED = "Label created successfully."

    def __init__(self, username):
        self.username = username
        self.master_token = None

    def save_master_token(self, password):
        keep = gkeepapi.Keep()
        success = keep.login(self.username, password)

        if success:
            master_token = keep.getMasterToken()
            keyring.set_password('google-keep-token', username, master_token)
            print(json.dumps({"code": self.CODE_SUCCESS, "message": self.MSG_MASTER_TOKEN_CREATED}))
        else:
            print(json.dumps({"code": self.CODE_ERROR, "message": self.MSG_FAILED_SAVE_MASTER_TOKEN}))
            return False

    def get_master_token(self):
        self.master_token = keyring.get_password('google-keep-token', self.username)
        if self.master_token:
            return self.master_token
        else:
            print(json.dumps({"code": self.CODE_ERROR, "message": self.MSG_NO_MASTER_TOKEN_FOUND}))
            return None

    def get_notes(self, note_id=None):
        master_token = self.get_master_token()
        if master_token:
            keep = gkeepapi.Keep()
            success = keep.resume(self.username, master_token)

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
                        print(json.dumps({"code": self.CODE_SUCCESS, "message": note_dict}, sort_keys=True))
                    else:
                        print(json.dumps({"code": self.CODE_ERROR, "message": self.MSG_NO_NOTE_FOUND}))
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
                    print(json.dumps({"code": self.CODE_SUCCESS, "message": note_json}, sort_keys=True))
                keep.sync()
            else:
                print(json.dumps({"code": self.CODE_ERROR, "message": self.MSG_FAILED_RESUME_SESSION}))

    def create_note(self, title, text=None, color=None, archived=False, pinned=False, labels=None, annotations=None, collaborators=None, list_items=None):
        if not title:
            print("Title cannot be empty.")
            return

        master_token = self.get_master_token()
        if master_token:
            keep = gkeepapi.Keep()
            success = keep.resume(self.username, master_token)

            if success:
                if list_items is not None:
                    # Create a list
                    list_items_tuples = [(item_text, item_checked) for item_text, item_checked in list_items]
                    gnote = keep.createList(title, list_items_tuples)
                    print(json.dumps({"code": self.CODE_SUCCESS, "message": self.MSG_LIST_CREATED}))
                else:
                    # Create a note
                    gnote = keep.createNote(title, text)
                    print(json.dumps({"code": self.CODE_SUCCESS, "message": self.MSG_NOTE_CREATED}))

                if color is not None:
                    gnote.color = color
                gnote.archived = archived
                gnote.pinned = pinned
                if labels is not None:
                    label = keep.findLabel(labels)
                    if label is None:
                        label = keep.createLabel(labels)
                    gnote.labels.add(label)
                if annotations is not None:
                    gnote.annotations = annotations
                if collaborators is not None:
                    gnote.collaborators = collaborators

                # Sync up changes
                keep.sync()
            else:
                print(json.dumps({"code": self.CODE_ERROR, "message": self.MSG_FAILED_RESUME_SESSION}))

    def search_notes(self, query=None, func=None, labels=None, colors=None, pinned=None, archived=None, trashed=None):
        master_token = self.get_master_token()
        if master_token:
            keep = gkeepapi.Keep()
            success = keep.resume(self.username, master_token)

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

                print(json.dumps({"code": self.CODE_SUCCESS, "message": note_json}, sort_keys=True))

                keep.sync()
            else:
                print(json.dumps({"code": self.CODE_ERROR, "message": self.MSG_FAILED_RESUME_SESSION}))

    def delete_note(self, note_id):
        master_token = self.get_master_token()
        if master_token:
            keep = gkeepapi.Keep()
            success = keep.resume(self.username, master_token)

            if success:
                gnote = keep.get(note_id)
                if gnote:
                    gnote.delete()
                    keep.sync()
                    print(json.dumps({"code": self.CODE_SUCCESS, "message": self.MSG_NOTE_DELETED}))
                else:
                    print(json.dumps({"code": self.CODE_ERROR, "message": self.MSG_NO_NOTE_FOUND}))
            else:
                print(json.dumps({"code": self.CODE_ERROR, "message": self.MSG_FAILED_RESUME_SESSION}))

    def restore_note(self, note_id):
        master_token = self.get_master_token()
        if master_token:
            keep = gkeepapi.Keep()
            success = keep.resume(self.username, master_token)

            if success:
                gnote = keep.get(note_id)
                if gnote:
                    gnote.undelete()
                    keep.sync()
                    print(json.dumps({"code": self.CODE_SUCCESS, "message": self.MSG_NOTE_RESTORED}))
                else:
                    print(json.dumps({"code": self.CODE_ERROR, "message": self.MSG_NO_NOTE_FOUND}))
            else:
                print(json.dumps({"code": self.CODE_ERROR, "message": self.MSG_FAILED_RESUME_SESSION}))

    def archive_note(self, note_id):
        master_token = self.get_master_token()
        if master_token:
            keep = gkeepapi.Keep()
            success = keep.resume(self.username, master_token)

            if success:
                gnote = keep.get(note_id)
                if gnote:
                    gnote.archive()
                    keep.sync()
                    print(json.dumps({"code": self.CODE_SUCCESS, "message": self.MSG_NOTE_ARCHIVED}))
                else:
                    print(json.dumps({"code": self.CODE_ERROR, "message": self.MSG_NO_NOTE_FOUND}))
            else:
                print(json.dumps({"code": self.CODE_ERROR, "message": self.MSG_FAILED_RESUME_SESSION}))

    def unarchive_note(self, note_id):
        master_token = self.get_master_token()
        if master_token:
            keep = gkeepapi.Keep()
            success = keep.resume(self.username, master_token)

            if success:
                gnote = keep.get(note_id)
                if gnote:
                    gnote.unarchive()
                    keep.sync()
                    print(json.dumps({"code": self.CODE_SUCCESS, "message": self.MSG_NOTE_UNARCHIVED}))
                else:
                    print(json.dumps({"code": self.CODE_ERROR, "message": self.MSG_NO_NOTE_FOUND}))
            else:
                print(json.dumps({"code": self.CODE_ERROR, "message": self.MSG_FAILED_RESUME_SESSION}))

    def pin_note(self, note_id):
        master_token = self.get_master_token()
        if master_token:
            keep = gkeepapi.Keep()
            success = keep.resume(self.username, master_token)

            if success:
                gnote = keep.get(note_id)
                if gnote:
                    gnote.pinned = True
                    keep.sync()
                    print(json.dumps({"code": self.CODE_SUCCESS, "message": self.MSG_NOTE_PINNED}))
                else:
                    print(json.dumps({"code": self.CODE_ERROR, "message": self.MSG_NO_NOTE_FOUND}))
            else:
                print(json.dumps({"code": self.CODE_ERROR, "message": self.MSG_FAILED_RESUME_SESSION}))

    def unpin_note(self, note_id):
        master_token = self.get_master_token()
        if master_token:
            keep = gkeepapi.Keep()
            success = keep.resume(self.username, master_token)

            if success:
                gnote = keep.get(note_id)
                if gnote:
                    gnote.pinned = False
                    keep.sync()
                    print(json.dumps({"code": self.CODE_SUCCESS, "message": self.MSG_NOTE_UNPINNED}))
                else:
                    print(json.dumps({"code": self.CODE_ERROR, "message": self.MSG_NO_NOTE_FOUND}))
            else:
                print(json.dumps({"code": self.CODE_ERROR, "message": self.MSG_FAILED_RESUME_SESSION}))

    def modify_note(self, note_id, title=None, text=None, color=None, labels=None, annotations=None, collaborators=None):
        master_token = self.get_master_token()
        if master_token:
            keep = gkeepapi.Keep()
            success = keep.resume(self.username, master_token)

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
                        label = keep.findLabel(labels)
                        gnote.labels.add(label)
                    if annotations is not None:
                        gnote.annotations = annotations
                    if collaborators is not None:
                        gnote.collaborators = collaborators

                    keep.sync()
                    print(json.dumps({"code": self.CODE_SUCCESS, "message": self.MSG_NOTE_MODIFIED}))
                else:
                    print(json.dumps({"code": self.CODE_ERROR, "message": self.MSG_NO_NOTE_FOUND}))
            else:
                print(json.dumps({"code": self.CODE_ERROR, "message": self.MSG_FAILED_RESUME_SESSION}))

    def add_item(self, note_id, item_text):
        master_token = self.get_master_token()
        if master_token:
            keep = gkeepapi.Keep()
            success = keep.resume(self.username, master_token)

            if success:
                gnote = keep.get(note_id)
                if gnote:
                    if isinstance(gnote, gkeepapi.node.List):
                        new_item = gnote.add(item_text)
                        keep.sync()
                        print(json.dumps({"code": self.CODE_SUCCESS, "message": self.MSG_ITEM_ADDED}))
                    else:
                        print(json.dumps({"code": self.CODE_ERROR, "message": self.MSG_NOTE_NOT_IN_LIST}))
                else:
                    print(json.dumps({"code": self.CODE_ERROR, "message": self.MSG_NO_NOTE_FOUND}))
            else:
                print(json.dumps({"code": self.CODE_ERROR, "message": self.MSG_FAILED_RESUME_SESSION}))

    def modify_item(self, note_id, item_id, text=None, checked=None, unchecked=None):
        master_token = self.get_master_token()
        if master_token:
            keep = gkeepapi.Keep()
            success = keep.resume(self.username, master_token)

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
                            print(json.dumps({"code": self.CODE_SUCCESS, "message": self.MSG_ITEM_MODIIED}))
                        else:
                            print(json.dumps({"code": self.CODE_ERROR, "message": self.MSG_NO_ITEM_FOUND}))
                    else:
                        print(json.dumps({"code": self.CODE_ERROR, "message": self.MSG_NOTE_NOT_IN_LIST}))
                else:
                    print(json.dumps({"code": self.CODE_ERROR, "message": self.MSG_NO_NOTE_FOUND}))
            else:
                print(json.dumps({"code": self.CODE_ERROR, "message": self.MSG_FAILED_RESUME_SESSION}))

    def delete_item(self, note_id, item_id):
        master_token = self.get_master_token()
        if master_token:
            keep = gkeepapi.Keep()
            success = keep.resume(self.username, master_token)

            if success:
                gnote = keep.get(note_id)
                if gnote:
                    if isinstance(gnote, gkeepapi.node.List):
                        item = gnote.get(item_id)
                        if item:
                            item.delete()
                            keep.sync()
                            print(json.dumps({"code": self.CODE_SUCCESS, "message": self.MSG_ITEM_DELETED}))
                        else:
                            print(json.dumps({"code": self.CODE_ERROR, "message": self.MSG_NO_ITEM_FOUND}))
                    else:
                        print(json.dumps({"code": self.CODE_ERROR, "message": self.MSG_NOTE_NOT_IN_LIST}))
                else:
                    print(json.dumps({"code": self.CODE_ERROR, "message": self.MSG_NO_NOTE_FOUND}))
            else:
                print(json.dumps({"code": self.CODE_ERROR, "message": self.MSG_FAILED_RESUME_SESSION}))

    def get_label(self, name=None, labelid=None):
        master_token = self.get_master_token()
        if master_token:
            keep = gkeepapi.Keep()
            success = keep.resume(self.username, master_token)

            if success:
                if name is not None:
                    label = keep.findLabel(name)
                    if not label:
                        print(json.dumps({"code": self.CODE_ERROR, "message": self.MSG_NO_LABEL_FOUND}))
                    else:
                        label_dict = {
                            "id": label.id,
                            "name": label.name,
                            "created": label.timestamps.created.timestamp(),
                            "updated": label.timestamps.updated.timestamp()
                        }
                        print(json.dumps({"code": self.CODE_SUCCESS, "message": label_dict}, sort_keys=True))
                elif labelid is not None:
                    label = keep.getLabel(labelid)
                    if not label:
                        print(json.dumps({"code": self.CODE_ERROR, "message": self.MSG_NO_LABEL_FOUND}))
                    else:
                        label_dict = {
                            "id": label.id,
                            "name": label.name,
                            "created": label.timestamps.created.timestamp(),
                            "updated": label.timestamps.updated.timestamp()
                        }
                        print(json.dumps({"code": self.CODE_SUCCESS, "message": label_dict}, sort_keys=True))
                else:
                    labels = keep.labels()
                    label_list = []
                    for label in labels:
                        label_dict = {
                            "id": label.id,
                            "name": label.name,
                            "created": label.timestamps.created.timestamp(),
                            "updated": label.timestamps.updated.timestamp()
                        }
                        label_list.append(label_dict)
                    if not label_list:
                        print(json.dumps({"code": self.CODE_ERROR, "message": self.MSG_NO_LABEL_FOUND}))
                    else:
                        print(json.dumps({"code": self.CODE_SUCCESS, "message": label_list}, sort_keys=True))
            else:
                print(json.dumps({"code": self.CODE_ERROR, "message": self.MSG_FAILED_RESUME_SESSION}))

    def create_label(self, name):
        master_token = self.get_master_token()
        if master_token:
            keep = gkeepapi.Keep()
            success = keep.resume(self.username, master_token)

            if success:
                label = keep.findLabel(name, create=True)
                #label = keep.createLabel(name)
                label_list = []
                label_dict = {
                    "id": label.id,
                    "name": label.name
                }
                label_list.append(label_dict)
                if not label_list:
                    print(json.dumps({"code": self.CODE_ERROR, "message": self.MSG_NO_LABEL_FOUND}))
                else:
                    print(json.dumps({"code": self.CODE_SUCCESS, "message": label_list}, sort_keys=True))
            else:
                print(json.dumps({"code": self.CODE_ERROR, "message": self.MSG_FAILED_RESUME_SESSION}))
        else:
            print(json.dumps({"code": self.CODE_ERROR, "message": self.MSG_NO_MASTER_TOKEN_FOUND}))

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
    parser_create_note = subparsers.add_parser("create_note", help="Create a new note or list")
    parser_create_note.add_argument("--title", help="Title of the note or list")
    parser_create_note.add_argument("--text", help="Text of the note")
    parser_create_note.add_argument("--color", help="Color of the note")
    parser_create_note.add_argument("--archived", action="store_true", help="Set archived status for the item")
    parser_create_note.add_argument("--unarchived", action="store_true", help="Set unarchived status for the item")
    parser_create_note.add_argument("--pinned", action="store_true", help="Set pinned status for the item")
    parser_create_note.add_argument("--unpinned", action="store_true", help="Set unpinned status for the item")
    parser_create_note.add_argument("--labels", help="Labels for the note")
    parser_create_note.add_argument('--annotations', help='New annotations for the note.')
    parser_create_note.add_argument('--collaborators', help='New collaborators for the note.')
    parser_create_note.add_argument("--list", nargs="+", action="append", metavar="ITEM", help="Items of the list in the format 'text,checked'")

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

    # Commande "get_label"
    parser_get_label = subparsers.add_parser("get_label", help="Get labels")
    parser_get_label.add_argument("--name", help="Name of the label to find")
    parser_get_label.add_argument("--id", help="Name of the label to find")

    # Commande "create_label"
    parser_add_label = subparsers.add_parser("create_label", help="Get labels")
    parser_add_label.add_argument("name", help="Name of the label to add")

    args = parser.parse_args()
    
    manager = GoogleKeepManager(args.username)

    # Vérifier si l'argument --username est fourni
    if not args.username:
        print("Erreur : l'argument --username est requis.")
        return

    # Exécuter la commande appropriée en fonction des arguments fournis
    if args.command == "save_master_token":
        manager.save_master_token(args.username, args.password)
    elif args.command == "get_notes":
        if hasattr(args, 'note_id') and args.note_id:
            manager.get_notes(args.note_id)
        else:
            manager.get_notes()

    elif args.command == "create_note":
        if args.list:
            list_items = []
            for item_str in args.list[0]:
                item_parts = item_str.split(",")
                if len(item_parts) == 2:
                    item_text = item_parts[0].strip()
                    item_checked = item_parts[1].strip().lower() == "true"
                    list_items.append((item_text, item_checked))
            manager.create_note(
                args.title,
                list_items=list_items,
                color=args.color,
                archived=args.archived and not args.unarchived,
                pinned=args.pinned and not args.unpinned,
                labels=args.labels,
                annotations=args.annotations,
                collaborators=args.collaborators
            )
        else:
            manager.create_note(
                args.title,
                text=args.text,
                color=args.color,
                archived=args.archived and not args.unarchived,
                pinned=args.pinned and not args.unpinned,
                labels=args.labels,
                annotations=args.annotations,
                collaborators=args.collaborators
            )
    elif args.command == "search_notes":
        manager.search_notes(args.query)
    elif args.command == "delete_note":
        if not args.note_id:
            print("Erreur : l'identifiant de la note est requis.")
            return
        manager.delete_note(args.note_id)
    elif args.command == "restore_note":
        if not args.note_id:
            print("Erreur : l'identifiant de la note est requis.")
            return
        manager.restore_note(args.note_id)
    elif args.command == "archive_note":
        if not args.note_id:
            print("Erreur : l'identifiant de la note est requis.")
            return
        manager.archive_note(args.note_id)
    elif args.command == "unarchive_note":
        if not args.note_id:
            print("Erreur : l'identifiant de la note est requis.")
            return
        manager.unarchive_note(args.note_id)
    elif args.command == "pin_note":
        if not args.note_id:
            print("Erreur : l'identifiant de la note est requis.")
            return
        manager.pin_note(args.note_id)
    elif args.command == "unpin_note":
        if not args.note_id:
            print("Erreur : l'identifiant de la note est requis.")
            return
        manager.unpin_note(args.note_id)
    elif args.command == "modify_note":
        if not args.note_id:
            print("Erreur : l'identifiant de la note est requis.")
            return
        manager.modify_note(args.note_id, args.title, args.text, args.color, args.labels, args.annotations, args.collaborators)
    elif args.command == "modify_item":
        if not args.note_id:
            print("Erreur : l'identifiant de la note est requis.")
            return
        if not args.item_id:
            print("Erreur : l'identifiant de l'item est requis.")
            return
        if args.checked:
            manager.modify_item(args.note_id, args.item_id, text=args.text, checked=True)
        elif args.unchecked:
            manager.modify_item(args.note_id, args.item_id, text=args.text, unchecked=True)
        else:
            manager.modify_item(args.note_id, args.item_id, text=args.text)
    elif args.command == "add_item":
        if not args.note_id:
            print("Erreur : l'identifiant de la note est requis.")
            return
        manager.add_item(args.note_id, args.text)
    elif args.command == "delete_item":
        if not args.note_id:
            print("Erreur : l'identifiant de la note est requis.")
            return
        if not args.item_id:
            print("Erreur : l'identifiant de l'item est requis.")
            return
        manager.delete_item(args.note_id, args.item_id)
    elif args.command == "get_label":
        if hasattr(args, 'name') and args.name:
            manager.get_label(args.name)
        elif hasattr(args, 'id') and args.id:
            manager.get_label(args.id)
        else:
            manager.get_label()
    elif args.command == "create_label":
        if not args.name:
            print("Erreur : le nom de l'étiquette est requis.")
            return
        manager.create_label(args.name)
    else:
        print("Invalid command")

if __name__ == "__main__":
    main()