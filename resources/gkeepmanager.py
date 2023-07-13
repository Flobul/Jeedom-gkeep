#!/usr/bin/env python3

"""
Auteur: Flobul <flobul.jeedom@gmail.com>
Version: 1.1
Description: This script manages Google Keep notes using the gkeepapi library. It allows you to interact with your Google Keep account, create, retrieve, update, and delete notes, as well as manage labels and annotations. The script uses the keyring library to securely store authentication credentials. It provides a command-line interface with various options for performing different operations on your Google Keep notes.
"""

import keyring
import gkeepapi
import json
import urllib.request
import argparse
import os
import mimetypes
import time

class GoogleKeepManager:
    CODE_SUCCESS = 0
    CODE_ERROR = -1

    MSG_MASTER_TOKEN_CREATED = "Master token created successfully."
    MSG_FAILED_SAVE_MASTER_TOKEN = "Failed to save master token."
    MSG_NO_NOTE_FOUND = "No note found with the specified ID."
    MSG_FAILED_RESUME_SESSION = "Failed to resume session. Please check credentials and master token."
    MSG_LIST_CREATED = "List created successfully."
    MSG_NOTE_CREATED = "Note created successfully."
    MSG_NOTE_DOWNLOADED = "Note downloaded successfully."
    MSG_NOTE_DELETED = "Note has been deleted."
    MSG_NOTE_RESTORED = "Note has been restored."
    MSG_NOTE_ARCHIVED = "Note has been archived."
    MSG_NOTE_UNARCHIVED = "Note has been unarchived."
    MSG_NOTE_PINNED = "Note has been pinned."
    MSG_NOTE_UNPINNED = "Note has been unpinned."
    MSG_NOTE_MODIFIED = "Note has been modified."
    MSG_NOTE_FOUND = "Notes has been found."
    MSG_NOTE_NOT_IN_LIST = "The note with the specified ID is not a list."
    MSG_NOTES_DOWNLOADED = "Notes downloaded successfully."
    MSG_ITEM_ADDED = "Item added successfully."
    MSG_ITEM_MODIIED = "Item has been modified."
    MSG_ITEM_DELETED = "Item has been deleted."
    MSG_NO_ITEM_FOUND = "No item found with the specified ID."
    MSG_NO_LABEL_FOUND = "No label found."
    MSG_LABEL_CREATED = "Label created successfully."
    MSG_LABEL_FOUND = "Label has been found."
    ERROR_MISSING_NOTE_ID = "Error: note ID required."
    ERROR_MISSING_ITEM_ID = "Error: item ID required."
    ERROR_MISSING_LABEL_NAME ="Error: label name required."
    ERROR_INVALID_CMD = "Error: Invalid command."
    ERROR_MISSING_USERNAME = "Error: Username is required."

    def __init__(self, username):
        self.path = '/var/www/html/plugins/gkeep/data'
        if not os.path.exists(self.path):
            os.mkdir(self.path)
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

    def detect_file_type(self, file_path):
        file_signatures = {
            b"\xFF\xD8\xFF": "image/jpeg",
            b"\x89\x50\x4E\x47\x0D\x0A\x1A\x0A": "image/png",
            b"\x47\x49\x46\x38\x37\x61": "image/gif",
            b"\x49\x44\x33": "audio/mp3",
            b"\x66\x74\x79\x70\x4D\x34\x41\x20": "audio/aac",
            b"\x00\x00\x00\x18\x66\x74\x79\x70\x33\x67\x70\x34": "video/3gpp",
            b"\x00\x00\x00\x14\x66\x74\x79\x70\x4D\x34\x41\x20": "video/mp4",
            b"\x52\x49\x46\x46": "image/webp",
            b"\x00\x00\x00\x0C\x4A\x58\x4C\x20\x0D\x0A\x87\x0A": "image/jxl",
            b"\x00\x00\x01\xB3": "video/mpeg",
            b"\x00\x00\x01\xBA": "video/mpeg",
            b"\x00\x00\x00\x0C\x6A\x50\x20\x20\x0D\x0A\x87\x0A": "image/jp2",
            b"\xFF\x4F\xFF\x51": "image/jp2",
            b"\x02\x64\x73\x73": "audio/dss",
            b"RIFF\x00\x00\x00\x00WEBPVP8 ": "image/webp",
            b"\x1A\x45\xDF\xA3": "video/webm",
            b"BM": "image/bmp",
            b"II\x2A\x00": "image/tiff",
            b"MM\x00\x2A": "image/tiff",
            b"OggS": "audio/ogg",
            b"fLaC": "audio/flac",
            b"gimp xcf": "image/x-xcf",
            b"#!SILK": "audio/silk",
            b"\x25\x62\x69\x74\x6D\x61\x70": "image/vnd.microsoft.icon",
            b"\x00\x00\x00\x20\x66\x74\x79\x70\x68\x65\x69\x63": "image/heic",
            b"\x00\x00\x00\x0C\x6A\x50\x20\x20": "image/jp2",
            b"\x00\x00\x00\x14\x66\x74\x79\x70": "video/3gpp",
            b"\x00\x00\x00\x14\x66\x74\x79\x70\x69\x73\x6F\x6D": "video/mp4",
            b"\x00\x00\x00\x14\x66\x74\x79\x70": "video/3gpp",
            b"\x00\x00\x00\x18\x66\x74\x79\x70": "video/mp4",
            b"\x00\x00\x00\x1C\x66\x74\x79\x70": "video/mp4",
            b"\x00\x00\x00\x20\x66\x74\x79\x70": "video/3gpp2",
            b"\x00\x00\x00\x20\x66\x74\x79\x70\x4D\x34\x41": "video/quicktime",
            b"\x00\x00\x00\x20\x66\x74\x79\x70": "video/3gpp2",
            b"\x00\x00\x01\xB3": "video/mpeg",
            b"\x00\x00\x01\xBA": "video/mpeg",
            b"\x0C\xED": "image/tiff",
            b"\x1A\x45\xDF\xA3": "video/webm",
            b"\x1A\x45\xDF\xA3": "video/x-matroska",
            b"\x1A\x45\xDF\xA3\x93\x42\x82\x88": "video/x-matroska",
            b"\x23\x21\x41\x4D\x52": "audio/amr",
            b"\x23\x21\x53\x49\x4C\x4B\x0A": "audio/x-speex",
            b"\x23\x3F\x52\x41\x44\x49\x41\x4E": "image/vnd.radiance",
            b"\x2E\x52\x4D\x46\x00\x00\x00\x12": "audio/vnd.rn-realaudio",
            b"\x2E\x72\x61\xFD\x00": "audio/vnd.rn-realaudio: ",
            b"\x2E\x73\x6E\x64": "audio/basic",
            b"\x30\x26\xB2\x75\x8E\x66\xCF\x11": "video/x-ms-wmv",
            b"\x42\x4D": "image/bmp",
            b"\x42\x50\x47\xFB": "image/bpg",
            b"\x47\x49\x46\x38": "image/gif",
            b"\x49\x20\x49": "image/tiff",
            b"\x49\x44\x33": "audio/mpeg",
            b"\x49\x44\x33\x03\x00\x00\x00": "audio/mpeg",
            b"\x49\x49\x2A\x00": "image/tiff",
            b"\x4D\x4D\x00\x2A": "image/tiff",
            b"\x4D\x4D\x00\x2B": "image/tiff",
            b"\x4D\x54\x68\x64": "audio/midi",
            b"\x4D\x54\x68\x64": "audio/midi",
            b"\x4E\x45\x53\x4D\x1A\x01": "audio/x-nsf",
            b"\x4F\x67\x67\x53\x00\x02\x00\x00": "audio/ogg",
            b"\x50\x35\x0A": "image/x-portable-graymap",
            b"\x57\x41\x56\x45\x66\x6D\x74\x20": "audio/x-wav",
            b"\x57\x45\x42\x50": "image/webp",
            b"\x57\x4D\x4D\x50": "audio/mpeg",
            b"\x66\x4C\x61\x43\x00\x00\x00\x22": "audio/flac",
            b"\x66\x74\x79\x70\x33\x67\x70\x35": "video/mp4",
            b"\x66\x74\x79\x70\x4D\x34\x41\x20": "audio/x-m4a",
            b"\x66\x74\x79\x70\x4D\x34\x56\x20": "video/mp4",
            b"\x66\x74\x79\x70\x4D\x53\x4E\x56": "video/mp4",
            b"\x66\x74\x79\x70\x69\x73\x6F\x6D": "video/mp4",
            b"\x66\x74\x79\x70\x6D\x70\x34\x32": "video/mp4",
            b"\x66\x74\x79\x70\x71\x74\x20\x20": "video/quicktime",
            b"\x69\x63\x6E\x73": "image/x-icon",
            b"\x6D\x6F\x6F\x76": "video/quicktime",
            b"\x66\x72\x65\x65": "video/quicktime",
            b"\x6D\x64\x61\x74": "video/quicktime",
            b"\x77\x69\x64\x65": "video/quicktime",
            b"\x70\x6E\x6F\x74": "video/quicktime",
            b"\x73\x6B\x69\x70": "video/quicktime",
            b"\x89\x50\x4E\x47\x0D\x0A\x1A\x0A": "image/png",
            b"\x97\x4A\x42\x32\x0D\x0A\x1A\x0A": "image/x-jbig2",
            b"\xAB\x4B\x54\x58\x20\x31\x31\xBB\x0D\x0A\x1A\x0A": "image/ktx",
            b"\xFF\xD8": "image/jpeg",
            b"\xFF\xD8\xFF": "image/jpeg",
            b"\xFF\xF1": "audio/aac",
            b"\xFF\xF9": "audio/aac"
        }

        with open(file_path, "rb") as file:
            # Lecture des premiers octets du fichier
            file_header = file.read(8)

        # Recherche de correspondances entre l'en-tête du fichier et les signatures connues
        for signature, file_type in file_signatures.items():
            if file_header.startswith(signature):
                return file_type

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
                        annotations = [annotation.save() for annotation in gnote.annotations.all()]
                        blobs = self.process_blobs(gnote.blobs, keep, gnote)
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
                            "collaborators": gnote.collaborators.all(),
                            "annotations": annotations,
                            "blobs": blobs
                        }
                        if isinstance(gnote, gkeepapi.node.List):
                            items = [{"id": item.id, "text": item.text, "checked": item.checked, "sort": item.sort} for item in gnote.items]
                            note_dict["list"] = items

                        print(json.dumps({"code": self.CODE_SUCCESS, "message": self.MSG_NOTE_DOWNLOADED, "result": note_dict}))
                    else:
                        print(json.dumps({"code": self.CODE_ERROR, "message": self.MSG_NO_NOTE_FOUND}))
                else:
                    gnotes = keep.all()
                    note_json = []
                    for note in gnotes:
                        annotations = [annotation.save() for annotation in note.annotations.all()]
                        blobs = self.process_blobs(note.blobs, keep, note)
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
                            "collaborators": note.collaborators.all(),
                            "annotations": annotations,
                            "blobs": blobs
                        }
                        if isinstance(note, gkeepapi.node.List):
                            items = [{"id": item.id, "text": item.text, "checked": item.checked, "sort": item.sort} for item in note.items]
                            note_dict["list"] = items

                        note_json.append(note_dict)
                    print(json.dumps({"code": self.CODE_SUCCESS, "message": self.MSG_NOTES_DOWNLOADED, "result": note_json}))
                keep.sync()
            else:
                print(json.dumps({"code": self.CODE_ERROR, "message": self.MSG_FAILED_RESUME_SESSION}))

    def process_blobs(self, blobs, keep, note):
        result = []
        for idx, blob in enumerate(blobs):
            url = keep.getMediaLink(blob)
            file_name = f"{note.id}_{int(time.time())}"
            file = os.path.join(self.path, file_name)
            try:
                urllib.request.urlretrieve(url, file)
            except Exception as e:
                logging.error(f"Error downloading blob file: {e}")
                continue
            
            if os.path.isfile(file):
                mime_type = self.detect_file_type(file)
                extension = mimetypes.guess_extension(mime_type)
                os.rename(file, file + extension)
                blob_dict = {
                    "url": url,
                    "file": file + extension,
                    "extension": extension,
                    "mimetype": mime_type
                }
                result.append(blob_dict)
        return result

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
                    label = keep.findLabel(labels, create=True)
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
                            "collaborators": note.collaborators.all(),
                            "annotations": note.annotations.all()
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
                            "collaborators": note.collaborators.all(),
                            "annotations": note.annotations.all()
                        }
                    note_json.append(note_dict)
                print(json.dumps({"code": self.CODE_SUCCESS, "message": self.MSG_NOTE_FOUND, "result": note_json}))
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
                        label = keep.findLabel(name, create=True)
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
                        print(json.dumps({"code": self.CODE_SUCCESS, "message": self.MSG_LABEL_FOUND, "result": label_dict}))
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
                        print(json.dumps({"code": self.CODE_SUCCESS, "message": self.MSG_LABEL_FOUND, "result": label_dict}))
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
                        print(json.dumps({"code": self.CODE_SUCCESS, "message": self.MSG_LABEL_FOUND, "result": label_list}))
            else:
                print(json.dumps({"code": self.CODE_ERROR, "message": self.MSG_FAILED_RESUME_SESSION}))

    def create_label(self, name):
        master_token = self.get_master_token()
        if master_token:
            keep = gkeepapi.Keep()
            success = keep.resume(self.username, master_token)

            if success:
                label = keep.findLabel(name, create=True)
                label_list = []
                label_dict = {
                    "id": label.id,
                    "name": label.name
                }
                label_list.append(label_dict)
                if not label_list:
                    print(json.dumps({"code": self.CODE_ERROR, "message": self.MSG_NO_LABEL_FOUND}))
                else:
                    keep.sync()
                    print(json.dumps({"code": self.CODE_SUCCESS, "message": self.MSG_LABEL_CREATED, "result": label_list}))
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
        print(self.ERROR_MISSING_USERNAME)
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
            print(self.ERROR_MISSING_NOTE_ID)
            return
        manager.delete_note(args.note_id)
    elif args.command == "restore_note":
        if not args.note_id:
            print(self.ERROR_MISSING_NOTE_ID)
            return
        manager.restore_note(args.note_id)
    elif args.command == "archive_note":
        if not args.note_id:
            print(self.ERROR_MISSING_NOTE_ID)
            return
        manager.archive_note(args.note_id)
    elif args.command == "unarchive_note":
        if not args.note_id:
            print(self.ERROR_MISSING_NOTE_ID)
            return
        manager.unarchive_note(args.note_id)
    elif args.command == "pin_note":
        if not args.note_id:
            print(self.ERROR_MISSING_NOTE_ID)
            return
        manager.pin_note(args.note_id)
    elif args.command == "unpin_note":
        if not args.note_id:
            print(self.ERROR_MISSING_NOTE_ID)
            return
        manager.unpin_note(args.note_id)
    elif args.command == "modify_note":
        if not args.note_id:
            print(self.ERROR_MISSING_NOTE_ID)
            return
        manager.modify_note(args.note_id, args.title, args.text, args.color, args.labels, args.annotations, args.collaborators)
    elif args.command == "modify_item":
        if not args.note_id:
            print(self.ERROR_MISSING_NOTE_ID)
            return
        if not args.item_id:
            print(self.ERROR_MISSING_ITEM_ID)
            return
        if args.checked:
            manager.modify_item(args.note_id, args.item_id, text=args.text, checked=True)
        elif args.unchecked:
            manager.modify_item(args.note_id, args.item_id, text=args.text, unchecked=True)
        else:
            manager.modify_item(args.note_id, args.item_id, text=args.text)
    elif args.command == "add_item":
        if not args.note_id:
            print(self.ERROR_MISSING_NOTE_ID)
            return
        manager.add_item(args.note_id, args.text)
    elif args.command == "delete_item":
        if not args.note_id:
            print(self.ERROR_MISSING_NOTE_ID)
            return
        if not args.item_id:
            print(self.ERROR_MISSING_ITEM_ID)
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
            print(self.ERROR_MISSING_LABEL_NAME)
            return
        manager.create_label(args.name)
    else:
        print(self.ERROR_INVALID_CMD)

if __name__ == "__main__":
    main()